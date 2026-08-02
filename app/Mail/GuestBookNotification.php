<?php

namespace App\Mail;

use App\Models\GuestBook;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class GuestBookNotification extends Mailable
{
    use Queueable, SerializesModels;

    public GuestBook $guestBook;

    public function __construct(GuestBook $guestBook)
    {
        $this->guestBook = $guestBook;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '🔔 Notifikasi Kunjungan Baru Buku Tamu Perpustakaan - ' . $this->guestBook->name,
        );
    }

    public function content(): Content
    {
        return new Content(
            htmlString: "
                <div style='font-family: Arial, sans-serif; max-w: 600px; margin: 0 auto; border: 1px solid #e2e8f0; border-radius: 16px; padding: 24px; background-color: #ffffff;'>
                    <div style='text-align: center; border-bottom: 2px solid #005da7; padding-bottom: 16px; margin-bottom: 20px;'>
                        <h2 style='color: #005da7; margin: 0;'>SDN 02 MARON - LENTERA MARON</h2>
                        <p style='color: #64748b; font-size: 13px; margin: 4px 0 0 0;'>Notifikasi Pengisian Buku Tamu Terbaru</p>
                    </div>

                    <p style='font-size: 14px; color: #334155;'>Halo Admin Perpustakaan,</p>
                    <p style='font-size: 14px; color: #334155;'>Terdapat pengunjung baru yang mengisi Buku Tamu di halaman publik website:</p>

                    <table style='width: 100%; font-size: 13px; border-collapse: collapse; margin: 20px 0;'>
                        <tr>
                            <td style='padding: 8px 12px; font-weight: bold; color: #475569; background: #f8fafc; border: 1px solid #e2e8f0; width: 35%;'>No. Kunjungan</td>
                            <td style='padding: 8px 12px; font-weight: bold; color: #005da7; border: 1px solid #e2e8f0;'>{$this->guestBook->visitor_no}</td>
                        </tr>
                        <tr>
                            <td style='padding: 8px 12px; font-weight: bold; color: #475569; background: #f8fafc; border: 1px solid #e2e8f0;'>Nama Pengunjung</td>
                            <td style='padding: 8px 12px; border: 1px solid #e2e8f0;'>{$this->guestBook->name}</td>
                        </tr>
                        <tr>
                            <td style='padding: 8px 12px; font-weight: bold; color: #475569; background: #f8fafc; border: 1px solid #e2e8f0;'>Instansi / Kelas</td>
                            <td style='padding: 8px 12px; border: 1px solid #e2e8f0;'>{$this->guestBook->institution}</td>
                        </tr>
                        <tr>
                            <td style='padding: 8px 12px; font-weight: bold; color: #475569; background: #f8fafc; border: 1px solid #e2e8f0;'>Keperluan</td>
                            <td style='padding: 8px 12px; border: 1px solid #e2e8f0;'>{$this->guestBook->purpose}</td>
                        </tr>
                        <tr>
                            <td style='padding: 8px 12px; font-weight: bold; color: #475569; background: #f8fafc; border: 1px solid #e2e8f0;'>Kesan & Pesan / Masukan</td>
                            <td style='padding: 8px 12px; border: 1px solid #e2e8f0; font-style: italic;'>\"" . ($this->guestBook->feedback ?: '-') . "\"</td>
                        </tr>
                        <tr>
                            <td style='padding: 8px 12px; font-weight: bold; color: #475569; background: #f8fafc; border: 1px solid #e2e8f0;'>Waktu Kunjungan</td>
                            <td style='padding: 8px 12px; border: 1px solid #e2e8f0;'>{$this->guestBook->date} - Jam {$this->guestBook->time}</td>
                        </tr>
                    </table>

                    <div style='text-align: center; margin-top: 24px; padding-top: 16px; border-t: 1px solid #e2e8f0; font-size: 11px; color: #94a3b8;'>
                        Email notifikasi ini dikirim secara otomatis oleh Sistem Perpustakaan Lentera Maron SDN 02 Maron.
                    </div>
                </div>
            "
        );
    }
}
