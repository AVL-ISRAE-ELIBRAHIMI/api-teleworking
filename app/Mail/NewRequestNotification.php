<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\TAM\RequestAsset;
use App\Models\TAM\Materiel;
use App\Models\TAM\Project;
use App\Models\Teleworking\Collaborateur;

class NewRequestNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $requestAsset;
    public $materiel;
    public $project;
    public $responsable;
    public $requestor; // Ajout de la propriété pour le demandeur
    public $requestUrl;

    public function __construct(RequestAsset $requestAsset, $responsable)
    {
        $this->requestAsset = $requestAsset;
        $this->materiel     = Materiel::findOrFail($requestAsset->materiel_id);
        $this->project      = Project::findOrFail($this->materiel->project_id);
        $this->responsable  = $responsable;

        // Charge le demandeur (objet Collaborateur), pas juste son ID
        $this->requestor = Collaborateur::find($requestAsset->requestor);
        // Lien direct vers la demande côté frontend
        $this->requestUrl = rtrim(config('app.frontend_url'), '/') . '/assets/requests';
    }

    public function build()
    {
        return $this->to($this->responsable->email)
            ->subject('Nouvelle demande de matériel')
            ->view('emails.NewRequestNotification')
            ->with([
                'responsable'   => $this->responsable,
                'materiel'      => $this->materiel,
                'requestAsset'  => $this->requestAsset,
                'project'       => $this->project,
                'requestor'     => $this->requestor,
                'requestUrl'   => $this->requestUrl,

            ]);
    }
}
