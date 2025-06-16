<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Reservation;
use App\Models\RentItem;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class reservationController extends Controller
{

    public function store(Request $request)
    {
        $data = $request->validate([
            'rent_item_id' => 'required|exists:rent_items,id',
            'start_date' => 'required|date|before_or_equal:end_date',
            'end_date' => 'required|date',
        ]);

        // Sprawdź konflikt z innymi potwierdzonymi rezerwacjami
        $conflict = Reservation::where('rent_item_id', $data['rent_item_id'])
            ->where('status', 'confirmed')
            ->where(function ($query) use ($data) {
                $query->whereBetween('start_date', [$data['start_date'], $data['end_date']])
                    ->orWhereBetween('end_date', [$data['start_date'], $data['end_date']])
                    ->orWhere(function($query) use ($data) {
                        $query->where('start_date', '<=', $data['start_date'])
                            ->where('end_date', '>=', $data['end_date']);
                    });
            })
            ->exists();

        if ($conflict) {
            return response()->json(['message' => 'Termin niedostępny'], 409);
        }

        $reservation = Reservation::create([
            'rent_item_id' => $data['rent_item_id'],
            'user_id' => auth()->id(),
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'status' => 'confirmed',
        ]);

        return response()->json($reservation, 201);
    }
    public function availableDates(Request $request, RentItem $rentItem)
    {
        $month = $request->query('month'); // format: 2025-06
        if (!$month || !preg_match('/^\d{4}-\d{2}$/', $month)) {
            return response()->json(['error' => 'Niepoprawny format miesiąca'], 400);
        }

        $startOfMonth = Carbon::parse($month . '-01')->startOfMonth();
        $endOfMonth = $startOfMonth->copy()->endOfMonth();

        // Pobierz zarezerwowane dni
        $reservations = Reservation::where('rent_item_id', $rentItem->id)
            ->where('status', 'confirmed')
            ->where(function ($query) use ($startOfMonth, $endOfMonth) {
                $query->whereBetween('start_date', [$startOfMonth, $endOfMonth])
                    ->orWhereBetween('end_date', [$startOfMonth, $endOfMonth])
                    ->orWhere(function ($query) use ($startOfMonth, $endOfMonth) {
                        $query->where('start_date', '<=', $startOfMonth)
                            ->where('end_date', '>=', $endOfMonth);
                    });
            })
            ->get();

        // Zbuduj listę niedostępnych dni
        $unavailableDates = [];

        foreach ($reservations as $res) {
            $period = CarbonPeriod::create($res->start_date, $res->end_date);
            foreach ($period as $date) {
                $unavailableDates[] = $date->format('Y-m-d');
            }
        }

        $unavailableDates = array_unique($unavailableDates);

        return response()->json([
            'month' => $month,
            'unavailable_dates' => array_values($unavailableDates),
        ]);
    }

    public function bookedDates(RentItem $rentItem)
    {
        $reservations = Reservation::where('rent_item_id', $rentItem->id)
            ->where('status', 'confirmed')
            ->orderBy('start_date')
            ->get(['start_date', 'end_date']);

        $booked = $reservations->map(function ($res) {
            return [
                'start' => $res->start_date->toDateString(),
                'end' => $res->end_date->toDateString(),
            ];
        });

        return response()->json([
            'rent_item_id' => $rentItem->id,
            'booked_dates' => $booked,
        ]);
    }

    public function confirm($id)
    {
        $reservation = Reservation::findOrFail($id);

        $conflict = Reservation::where('rent_item_id', $reservation->rent_item_id)
            ->where('status', 'confirmed')
            ->where(function ($query) use ($reservation) {
                $query->whereBetween('start_date', [$reservation->start_date, $reservation->end_date])
                    ->orWhereBetween('end_date', [$reservation->start_date, $reservation->end_date])
                    ->orWhere(function ($query) use ($reservation) {
                        $query->where('start_date', '<=', $reservation->start_date)
                            ->where('end_date', '>=', $reservation->end_date);
                    });
            })
            ->exists();

        if ($conflict) {
            return response()->json(['message' => 'Termin już zajęty'], 409);
        }

        $reservation->status = 'confirmed';
        $reservation->save();

        return response()->json(['message' => 'Rezerwacja potwierdzona']);
    }

    public function cancel($id)
    {
        $reservation = Reservation::findOrFail($id);

        // Można dodać sprawdzenie uprawnień (auth()->id() == $reservation->user_id)

        $reservation->status = 'cancelled';
        $reservation->save();

        return response()->json(['message' => 'Rezerwacja anulowana']);
    }

    public function pendingReservationsForRentItem($rentItemId)
    {
        $reservations = Reservation::where('rent_item_id', $rentItemId)
            ->where('status', 'pending')
            ->with('user:id,name')
            ->orderBy('start_date')
            ->get(['id', 'rent_item_id', 'user_id', 'start_date', 'end_date', 'status']);

        return response()->json([
            'pending_reservations' => $reservations,
        ]);
    }

}
