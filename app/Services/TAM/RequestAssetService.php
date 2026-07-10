<?php

namespace App\Services\TAM;

use App\Models\TAM\Materiel;
use App\Models\TAM\RequestAsset;
use App\Exceptions\ReservationConflictException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class RequestAssetService
{
    /**
     * Statuts de RequestAsset considérés comme bloquants pour la disponibilité.
     */
    protected array $blockingStatuses = ['Pending', 'Reserved'];

    public function create(array $data, Request $request): RequestAsset
    {
        $collaborateur = $request->user();

        if ($this->hasOverlap($data['materiel_id'], $data['borrow_date'], $data['return_date'])) {
            throw new ReservationConflictException(
                'Ce matériel est déjà réservé sur cette période.'
            );
        }

        $requestAsset = RequestAsset::create([
            'materiel_id'   => $data['materiel_id'],
            'requestor'     => $data['requestor'] ?? $collaborateur?->id,
            'validator'     => $data['validator'] ?? $collaborateur?->id,
            'borrow_date'   => $data['borrow_date'],
            'return_date'   => $data['return_date'],
            'new_location'  => $data['new_location'],
            'remark'        => $data['remark'] ?? null,
            'status'        => 'Pending', 
        ]);

        // Mise à jour du matériel avec une valeur valide de l'enum materiels
        $materiel = Materiel::findOrFail($data['materiel_id']);
        $materiel->status = 'reserved'; //
        $materiel->save();

        return $requestAsset;
    }

    public function hasOverlap(int $materielId, string $borrowDate, string $returnDate, ?int $ignoreRequestId = null): bool
    {
        $query = RequestAsset::where('materiel_id', $materielId)
            ->whereIn('status', $this->blockingStatuses)
            ->where('borrow_date', '<=', $returnDate)
            ->where('return_date', '>=', $borrowDate);

        if ($ignoreRequestId) {
            $query->where('id', '!=', $ignoreRequestId);
        }

        return $query->exists();
    }

    public function reservedPeriods(int $materielId): array
    {
        return RequestAsset::where('materiel_id', $materielId)
            ->whereIn('status', $this->blockingStatuses)
            ->orderBy('borrow_date')
            ->get(['borrow_date', 'return_date'])
            ->map(fn (RequestAsset $r) => [
                'start' => Carbon::parse($r->borrow_date)->format('Y-m-d'),
                'end'   => Carbon::parse($r->return_date)->format('Y-m-d'),
            ])
            ->toArray();
    }

    public function collaboratorRequests(): Collection
    {
        $collaborateur = Auth::user();

        if (!$collaborateur) {
            return collect();
        }

        return RequestAsset::with('materiel')
            ->where('requestor', $collaborateur->id)
            ->orderByDesc('created_at')
            ->get()
            ->map(function (RequestAsset $requestAsset) {
                $materiel = $requestAsset->materiel;

                return [
                    'label' => $materiel?->label,
                    'avl_reference' => $materiel?->avl_reference ?? $materiel?->avl_ref,
                    'serial_number' => $materiel?->serial_number ?? $materiel?->serial_num,
                    'borrow_date' => $requestAsset->borrow_date?->format('Y-m-d'),
                    'return_date' => $requestAsset->return_date?->format('Y-m-d'),
                    'remark' => $requestAsset->remark,
                    'new_location' => $requestAsset->new_location,
                    'status' => $requestAsset->status,
                ];
            });
    }
}