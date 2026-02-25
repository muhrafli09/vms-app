<?php

namespace App\Http\Controllers;

use App\Models\{Kiosk, Employee, Visitor, Visit};
use Illuminate\Http\Request;

class KioskController extends Controller
{
    public function checkVisitor(Request $request, $token)
    {
        $kiosk = Kiosk::where('token', $token)->firstOrFail();
        
        if (!$kiosk->is_active) {
            return response()->json(['error' => 'Kiosk is disabled'], 403);
        }
        
        $phone = $request->input('phone');
        $email = $request->input('email');
        
        if ($phone) {
            $visitor = Visitor::where('phone', $phone)->first();
            
            if ($visitor) {
                return response()->json([
                    'found' => true,
                    'data' => [
                        'name' => $visitor->name,
                        'email' => $visitor->email,
                        'company' => $visitor->company,
                    ]
                ]);
            }
        }
        
        if ($email) {
            $emailExists = Visitor::where('email', $email)
                ->where('phone', '!=', $phone)
                ->exists();
            
            if ($emailExists) {
                return response()->json([
                    'found' => false,
                    'email_taken' => true,
                    'message' => 'Email sudah digunakan oleh visitor lain'
                ]);
            }
        }
        
        return response()->json(['found' => false, 'email_taken' => false]);
    }
    
    public function index($token)
    {
        $kiosk = Kiosk::where('token', $token)->firstOrFail();
        
        if (!$kiosk->is_active) {
            abort(403, 'This kiosk is currently disabled');
        }
        
        return view('kiosk.index', compact('kiosk'));
    }
    
    public function showScanQr($token)
    {
        $kiosk = Kiosk::where('token', $token)->firstOrFail();
        
        if (!$kiosk->is_active) {
            abort(403, 'This kiosk is currently disabled');
        }
        
        return view('kiosk.scan-qr', compact('kiosk'));
    }
    
    public function showCheckin($token)
    {
        $kiosk = Kiosk::where('token', $token)->firstOrFail();
        
        if (!$kiosk->is_active) {
            abort(403, 'This kiosk is currently disabled');
        }
        
        $employees = Employee::with(['department', 'designation'])
            ->orderBy('first_name')
            ->get();
        
        return view('kiosk.checkin', compact('kiosk', 'employees'));
    }
    
    public function checkin(Request $request, $token)
    {
        $kiosk = Kiosk::where('token', $token)->firstOrFail();
        
        if (!$kiosk->is_active) {
            abort(403, 'This kiosk is currently disabled');
        }
        
        $validated = $request->validate([
            'phone' => 'required|max:255',
            'name' => 'required|max:255',
            'email' => 'nullable|email|max:255',
            'company' => 'nullable|max:255',
            'employee_id' => 'required|exists:employees,id',
            'purpose' => 'nullable|max:500',
            'photo' => 'required|string',
        ]);
        
        $visitor = Visitor::where('phone', $validated['phone'])->first();
        
        if ($visitor) {
            $visitor->update([
                'name' => $validated['name'],
                'email' => $validated['email'] ?? $visitor->email,
                'company' => $validated['company'] ?? $visitor->company,
            ]);
        } else {
            $visitor = Visitor::create([
                'phone' => $validated['phone'],
                'name' => $validated['name'],
                'email' => $validated['email'] ?? null,
                'company' => $validated['company'] ?? null,
            ]);
        }
        
        $photoPath = null;
        if ($validated['photo']) {
            try {
                $photoPath = $this->saveBase64Image($validated['photo'], 'visits');
            } catch (\Exception $e) {
                return back()->withErrors(['photo' => 'Failed to save photo']);
            }
        }
        
        Visit::create([
            'visitor_id' => $visitor->id,
            'employee_id' => $validated['employee_id'],
            'purpose' => $validated['purpose'] ?? null,
            'photo' => $photoPath,
            'arrival' => now(),
            'uuid' => \Illuminate\Support\Str::uuid(),
            'status' => 'checked_in',
        ]);
        
        return redirect()->route('kiosk.index', $token)->with('success', 'Check-in successful!');
    }
    
    public function scanQr(Request $request, $token)
    {
        $kiosk = Kiosk::where('token', $token)->firstOrFail();
        
        if (!$kiosk->is_active) {
            return response()->json(['error' => 'Kiosk is disabled'], 403);
        }
        
        $validated = $request->validate([
            'uuid' => 'required|string',
            'photo' => 'required|string',
        ]);
        
        $visit = Visit::where('uuid', $validated['uuid'])
            ->where('status', 'scheduled')
            ->with(['employee'])
            ->first();
        
        if (!$visit) {
            return response()->json(['error' => 'Appointment not found or already checked in'], 404);
        }
        
        // Save photo
        $photoPath = null;
        try {
            $photoPath = $this->saveBase64Image($validated['photo'], 'visits');
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to save photo'], 500);
        }
        
        // Update visit with photo and check-in
        $visit->update([
            'status' => 'checked_in',
            'arrival' => now(),
            'photo' => $photoPath,
        ]);
        
        return response()->json([
            'success' => true,
            'visitor' => $visit->visitor,
            'employee' => $visit->employee->full_name,
            'scheduled_time' => $visit->scheduled_time->format('M d, Y H:i'),
        ]);
    }
    
    private function saveBase64Image($base64String, $folder = 'photos')
    {
        if (preg_match('/^data:image\/(\w+);base64,/', $base64String, $type)) {
            $base64String = substr($base64String, strpos($base64String, ',') + 1);
            $type = strtolower($type[1]);
        } else {
            $type = 'png';
        }
        
        $image = base64_decode($base64String);
        
        if ($image === false) {
            throw new \Exception('Base64 decode failed');
        }
        
        $filename = uniqid() . '.' . $type;
        $path = $folder . "/" . date('Y/m');
        
        \Storage::disk('public')->makeDirectory($path);
        \Storage::disk('public')->put($path . '/' . $filename, $image);
        
        return $path . '/' . $filename;
    }
}
