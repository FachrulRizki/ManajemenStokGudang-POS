<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Shift;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ShiftController extends Controller
{
    /** Halaman daftar shift (riwayat) */
    public function index(Request $request)
    {
        $query = Shift::with('user')->withCount('transactions');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('shift_number', 'like', "%{$request->search}%")
                  ->orWhereHas('user', fn($u) => $u->where('name', 'like', "%{$request->search}%"));
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        $shifts   = $query->latest('opened_at')->paginate(15)->withQueryString();
        $kasirs   = \App\Models\User::where('is_active', true)->orderBy('name')->get();
        $myShift  = Auth::user()->activeShift();

        return view('pos.shifts.index', compact('shifts', 'kasirs', 'myShift'));
    }

    /** Buka shift baru */
    public function open(Request $request)
    {
        $user = Auth::user();

        if ($user->hasOpenShift()) {
            return back()->with('error', 'Anda masih memiliki shift yang aktif. Tutup shift terlebih dahulu.');
        }

        $data = $request->validate([
            'opening_cash' => ['required', 'numeric', 'min:0'],
            'notes'        => ['nullable', 'string'],
        ]);

        $shift = Shift::create([
            'shift_number' => Shift::generateShiftNumber(),
            'user_id'      => $user->id,
            'opened_at'    => now(),
            'opening_cash' => $data['opening_cash'],
            'notes'        => $data['notes'] ?? null,
            'status'       => 'open',
        ]);

        ActivityLog::log('create', 'shifts', "Buka shift: {$shift->shift_number}", $shift, [], $shift->toArray());

        return redirect()->route('pos.kasir')->with('success', "Shift {$shift->shift_number} berhasil dibuka. Selamat bekerja!");
    }

    /** Tutup shift */
    public function close(Request $request, Shift $shift)
    {
        if ($shift->user_id !== Auth::id() && !Auth::user()->isAdmin() && !Auth::user()->isManager()) {
            return back()->with('error', 'Anda tidak bisa menutup shift milik kasir lain.');
        }

        if ($shift->status === 'closed') {
            return back()->with('error', 'Shift sudah ditutup.');
        }

        $data = $request->validate([
            'closing_cash' => ['required', 'numeric', 'min:0'],
            'notes'        => ['nullable', 'string'],
        ]);

        $shift->recalculate();
        $shift->update([
            'closed_at'    => now(),
            'closing_cash' => $data['closing_cash'],
            'status'       => 'closed',
            'notes'        => $data['notes'] ?? $shift->notes,
        ]);

        // Hitung selisih setelah menutup
        $shift->cash_difference = $shift->closing_cash - $shift->expected_cash;
        $shift->save();

        ActivityLog::log('update', 'shifts', "Tutup shift: {$shift->shift_number}", $shift, [], $shift->toArray());

        return redirect()->route('shifts.index')->with('success', "Shift {$shift->shift_number} berhasil ditutup.");
    }

    /** Detail shift */
    public function show(Shift $shift)
    {
        $shift->load(['user', 'transactions.items', 'transactions.paymentMethod']);
        return view('pos.shifts.show', compact('shift'));
    }
}
