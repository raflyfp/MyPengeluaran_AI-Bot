<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\BotMessage;
use App\Models\Transaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $monthlySummary = Transaction::monthlySummaryFor($user);
        $income = (float) $monthlySummary['income_total'];
        $expense = (float) $monthlySummary['expense_total'];
        $savingsRate = $income > 0 ? max(0, round((($income - $expense) / $income) * 100)) : 0;
        $latestTelegramMessage = BotMessage::query()
            ->where('user_id', $user->id)
            ->where('platform', 'telegram')
            ->latest()
            ->first();
        $telegramMessages = BotMessage::query()
            ->where('user_id', $user->id)
            ->where('platform', 'telegram')
            ->count();

        return view('profile.index', [
            'user' => $user,
            'profileStats' => [
                'savings_rate' => $savingsRate,
                'connected_bots' => $telegramMessages > 0 ? 1 : 0,
                'monthly_transactions' => Transaction::query()->forUser($user)->forMonth()->count(),
            ],
            'telegramStatus' => [
                'connected' => $telegramMessages > 0,
                'last_sync' => $latestTelegramMessage?->created_at?->diffForHumans() ?? 'Waiting for first message',
            ],
        ]);
    }

    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
