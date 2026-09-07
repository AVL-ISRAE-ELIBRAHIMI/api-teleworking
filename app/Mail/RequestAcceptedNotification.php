<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\TAM\RequestAsset;
use App\Models\TAM\Materiel;
use App\Models\TAM\Project;
use App\Models\Teleworking\Collaborateur;

class RequestAcceptedNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $requestAsset;
    public $materiel;
    public $project;
    public $requestor;
    public $validator;
    public $requestUrl;

    public function __construct(RequestAsset $requestAsset, $validator)
    {
        $this->requestAsset = $requestAsset;
        $this->materiel     = Materiel::findOrFail($requestAsset->materiel_id);
        $this->project      = Project::findOrFail($this->materiel->project_id);
        $this->requestor    = Collaborateur::find($requestAsset->requestor);
        $this->validator    = $validator;

        $this->requestUrl = rtrim(config('app.frontend_url'), '/') . '/assets/requests/collaborator';
    }

    public function build()
    {
        return $this->to($this->requestor->email)
                    ->subject('Votre demande de matériel a été acceptée')
                    ->view('emails.request_accepted')
                    ->with([
                        'requestor'    => $this->requestor,
                        'validator'    => $this->validator,
                        'materiel'     => $this->materiel,
                        'requestAsset' => $this->requestAsset,
                        'project'      => $this->project,
                        'requestUrl'   => $this->requestUrl,
                    ]);
    }
}