<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Backup;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;

class BackupController extends Controller
{
    public function index()
    {
        $backups = Backup::with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('backups.index', compact('backups'));
    }

    public function create()
    {
        Artisan::call('panel:backup');
        ActivityLog::log('create_backup', 'Manual backup created');

        return back()->with('success', 'Backup berhasil dibuat.');
    }

    public function download(Backup $backup)
    {
        $fullPath = storage_path('app/' . $backup->file_path);
        if (!file_exists($fullPath)) {
            return back()->with('error', 'File backup tidak ditemukan.');
        }

        return response()->download($fullPath, $backup->file_name);
    }

    public function destroy(Backup $backup)
    {
        $fullPath = storage_path('app/' . $backup->file_path);
        if (file_exists($fullPath)) {
            unlink($fullPath);
        }

        $name = $backup->file_name;
        $backup->delete();
        ActivityLog::log('delete_backup', "Deleted backup: {$name}");

        return back()->with('success', 'Backup berhasil dihapus.');
    }
}
