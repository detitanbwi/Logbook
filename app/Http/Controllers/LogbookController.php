<?php

namespace App\Http\Controllers;

use App\Models\Logbook;
use App\Models\LogbookItem;
use App\Models\LogbookAttachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class LogbookController extends Controller
{
    public function index()
    {
        $logbooks = auth()->user()->logbooks()
            ->with(['items.kpi', 'latestReview'])
            ->latest('start_time')
            ->paginate(10);

        return view('logbooks.index', [
            'logbooks' => $logbooks,
            'title' => 'Logbook Saya',
            'active' => 'logbooks'
        ]);
    }

    public function create()
    {
        $user = auth()->user();
        $assignedKpis = $user->kpis;

        return view('logbooks.create', [
            'kpis' => $assignedKpis,
            'title' => 'Buat Logbook',
            'active' => 'logbooks'
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'latitude' => 'nullable|string',
            'longitude' => 'nullable|string',
            'start_time' => 'required',
            'end_time' => 'required',
            'items' => 'required|array|min:1',
            'items.*.kpi_id' => 'required|exists:kpis,id',
            'items.*.details' => 'required|string',
            'daily_report' => 'required|string',
            'main_photo' => 'nullable|image', // Removed max:2048 to allow all camera sizes
            'attachments.*' => 'nullable|file|max:10240', // Increased to 10MB
        ]);

        // Combine with today's date if only time is provided
        $date = $request->date ?? now()->toDateString();
        $startTime = Carbon::parse($date . ' ' . $request->start_time);
        $endTime = Carbon::parse($date . ' ' . $request->end_time);

        $logbook = Logbook::create([
            'employee_id' => auth()->id(),
            'supervisor_id' => auth()->user()->supervisor_id,
            'latitude' => $request->latitude ?? 0,
            'longitude' => $request->longitude ?? 0,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'daily_report' => $request->daily_report,
            'main_photo_path' => null,
            'status' => 'pending',
        ]);

        foreach ($request->items as $item) {
            LogbookItem::create([
                'logbook_id' => $logbook->id,
                'kpi_id' => $item['kpi_id'],
                'work_description' => $item['details']
            ]);
        }

        if ($request->hasFile('main_photo')) {
            $file = $request->file('main_photo');
            $filename = time() . '_' . uniqid() . '.jpg';
            $path = 'logbook_photos/' . $filename;
            
            // Image Optimization using Intervention Image v3
            $manager = new ImageManager(new Driver());
            $image = $manager->read($file);
            
            // Resize if too large (max width 1280px) while maintaining aspect ratio
            if ($image->width() > 1280) {
                $image->scale(width: 1280);
            }
            
            // Encode as JPEG with 60% quality
            $encoded = $image->toJpeg(60);
            
            Storage::disk('public')->put($path, $encoded);

            LogbookAttachment::create([
                'logbook_id' => $logbook->id,
                'file_path' => $path,
                'file_type' => 'photo'
            ]);
            $logbook->update(['main_photo_path' => $path]);
        }

        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('logbook_files', 'public');
                LogbookAttachment::create([
                    'logbook_id' => $logbook->id,
                    'file_path' => $path,
                    'file_type' => 'file'
                ]);
            }
        }

        return redirect()->route('logbooks.index')->with('success', 'Logbook berhasil dikirim.');
    }

    public function show(Logbook $logbook)
    {
        $this->authorizeAccess($logbook);
        $logbook->load(['items.kpi', 'attachments', 'latestReview.reviewer', 'employee', 'supervisor']);

        return view('logbooks.show', [
            'logbook' => $logbook,
            'title' => 'Detail Logbook',
            'active' => 'logbooks'
        ]);
    }

    private function authorizeAccess(Logbook $logbook)
    {
        $user = auth()->user();
        
        // Cast to int to prevent type mismatch (DB int vs session value)
        $userId = (int) $user->id;
        $employeeId = (int) $logbook->employee_id;
        $supervisorId = (int) $logbook->supervisor_id;
        
        // Owner of the logbook can always view it
        if ($employeeId === $userId) return;
        
        // Super Admin can view all
        if ($user->role === 'super_admin') return;
        
        // Supervisor/Admin can view if it's their subordinate's logbook
        if ($user->role !== 'staff' && $supervisorId === $userId) return;
        
        // Otherwise, access denied
        abort(403);
    }
}
