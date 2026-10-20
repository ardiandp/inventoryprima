<?php

namespace App\Notifications;

use App\Models\PurchaseRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Notifikasi perubahan status RPO.
 *
 * Dikirim ke pemohon saat RPO disubmit/ditinjau/direvisi/ditolak/disetujui,
 * dan ke tim Purchasing saat RPO baru diajukan.
 */
class PurchaseRequestStatusChanged extends Notification
{
    use Queueable;

    public function __construct(
        public PurchaseRequest $purchaseRequest,
        public string $fromStatus,
        public string $toStatus,
        public ?string $reason = null,
        public ?string $comment = null,
    ) {
    }

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'purchase_request_id' => $this->purchaseRequest->id,
            'display_number' => $this->purchaseRequest->display_number,
            'from_status' => $this->fromStatus,
            'to_status' => $this->toStatus,
            'status_label' => PurchaseRequest::statusLabel($this->toStatus),
            'reason' => $this->reason,
            'comment' => $this->comment,
            'url' => route('rpo.show', $this->purchaseRequest),
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $message = (new MailMessage)
            ->subject("RPO {$this->purchaseRequest->display_number}: ".
                PurchaseRequest::statusLabel($this->toStatus))
            ->greeting('Halo '.$notifiable->name.',')
            ->line('Permintaan pembelian (RPO) '.$this->purchaseRequest->display_number.
                ' berubah status menjadi '.PurchaseRequest::statusLabel($this->toStatus).'.');

        if ($this->reason) {
            $message->line('Alasan: '.$this->reason);
        }

        if ($this->comment) {
            $message->line('Komentar: '.$this->comment);
        }

        return $message->action('Lihat RPO', route('rpo.show', $this->purchaseRequest));
    }
}
