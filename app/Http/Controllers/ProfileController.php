<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\BotMessage;
use App\Models\Transaction;
use App\Services\TelegramAccountLink;
use App\Services\TelegramBotClient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function index(Request $request, TelegramAccountLink $telegramAccountLink): View
    {
        $user = $request->user();
        $telegramLink = $telegramAccountLink->forUser($user);
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
                'connected_bots' => filled($user->telegram_user_id) || filled($user->telegram_chat_id) ? 1 : 0,
                'monthly_transactions' => Transaction::query()->forUser($user)->forMonth()->count(),
            ],
            'telegramStatus' => [
                'connected' => filled($user->telegram_user_id) || filled($user->telegram_chat_id),
                'last_sync' => $latestTelegramMessage?->created_at?->diffForHumans() ?? 'Waiting for first message',
                'username' => $user->telegram_username,
                'link_command' => $telegramLink['command'],
                'link_url' => $telegramLink['url'],
                'link_expires_at' => $telegramLink['expires_at'],
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

    public function disconnectTelegram(Request $request): RedirectResponse
    {
        $request->user()->forceFill([
            'telegram_user_id' => null,
            'telegram_chat_id' => null,
            'telegram_username' => null,
            'telegram_link_token' => null,
            'telegram_link_token_expires_at' => null,
        ])->save();

        return Redirect::route('profile.index')
            ->with('status', 'telegram-disconnected');
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

    public function editBudget(Request $request): View
    {
        return view('profile.budget', [
            'user' => $request->user(),
        ]);
    }

    public function updateBudget(Request $request): RedirectResponse
    {
        $request->validate([
            'monthly_budget' => ['required', 'numeric', 'min:0'],
        ]);

        $request->user()->fill([
            'monthly_budget' => $request->input('monthly_budget'),
        ])->save();

        return Redirect::route('profile.budget')->with('status', 'budget-updated');
    }

    public function editPreferences(Request $request): View
    {
        return view('profile.preferences', [
            'user' => $request->user(),
        ]);
    }

    public function updatePreferences(Request $request): RedirectResponse
    {
        $request->validate([
            'currency' => ['required', 'string', 'max:10'],
            'language' => ['required', 'string', 'in:id,en'],
        ]);

        $request->user()->fill([
            'currency' => $request->input('currency'),
            'language' => $request->input('language'),
        ])->save();

        return Redirect::route('profile.preferences')->with('status', 'preferences-updated');
    }

    public function exportTelegram(Request $request, TelegramBotClient $telegram): RedirectResponse
    {
        $user = $request->user();

        if (blank($user->telegram_user_id) && blank($user->telegram_chat_id)) {
            return Redirect::route('profile.index')
                ->with('error', 'Silakan sambungkan Telegram terlebih dahulu di bagian Connections.');
        }

        $chatId = $user->telegram_chat_id ?: $user->telegram_user_id;

        // Fetch user data
        $monthlySummary = Transaction::monthlySummaryFor($user);
        $income = (float) $monthlySummary['income_total'];
        $expense = (float) $monthlySummary['expense_total'];
        $balance = $income - $expense;

        // Get top categories
        $topCategories = Transaction::query()
            ->forUser($user)
            ->forMonth()
            ->ofType('expense')
            ->join('categories', 'transactions.category_id', '=', 'categories.id')
            ->selectRaw('categories.name, SUM(transactions.amount) as total')
            ->groupBy('categories.name')
            ->orderByDesc('total')
            ->limit(3)
            ->get();

        // Create image with GD
        $width = 600;
        $height = 400;
        $im = imagecreatetruecolor($width, $height);

        // Colors
        $bg = imagecolorallocate($im, 255, 241, 246); // #FFF1F6
        $white = imagecolorallocate($im, 255, 255, 255);
        $pink = imagecolorallocate($im, 184, 51, 106); // #B8336A
        $dark = imagecolorallocate($im, 75, 39, 53); // #4B2735
        $green = imagecolorallocate($im, 46, 159, 134); // #2E9F86
        $gray = imagecolorallocate($im, 155, 122, 130); // #9B7A82
        $pinkBorder = imagecolorallocate($im, 245, 201, 214); // #F5C9D6

        // Fill background
        imagefill($im, 0, 0, $bg);

        // Draw header card
        imagefilledrectangle($im, 20, 20, 580, 100, $pink);
        // Header text (GD built-in font size 5 is large, size 3 is medium, size 2 is small)
        imagestring($im, 5, 40, 35, "MYPENGELUARAN FINANCIAL REPORT", $white);
        imagestring($im, 3, 40, 65, "Month: " . now()->format('F Y') . " | User: " . $user->name, $white);

        // Draw financial stats cards
        // Balance Card
        imagefilledrectangle($im, 20, 120, 290, 240, $white);
        imagerectangle($im, 20, 120, 290, 240, $pinkBorder);
        imagestring($im, 4, 40, 140, "Total Balance", $dark);
        imagestring($im, 5, 40, 170, "Rp " . number_format($balance, 0, ',', '.'), $pink);

        // Income & Expense Card
        imagefilledrectangle($im, 310, 120, 580, 240, $white);
        imagerectangle($im, 310, 120, 580, 240, $pinkBorder);
        imagestring($im, 3, 330, 135, "Income:", $dark);
        imagestring($im, 4, 330, 155, "Rp " . number_format($income, 0, ',', '.'), $green);

        imagestring($im, 3, 330, 185, "Expense:", $dark);
        imagestring($im, 4, 330, 205, "Rp " . number_format($expense, 0, ',', '.'), $pink);

        // Top Category Card
        imagefilledrectangle($im, 20, 260, 580, 380, $white);
        imagerectangle($im, 20, 260, 580, 380, $pinkBorder);
        imagestring($im, 4, 40, 275, "Top Spending Categories", $dark);

        $y = 305;
        if ($topCategories->isEmpty()) {
            imagestring($im, 3, 40, $y, "No transactions recorded this month.", $gray);
        } else {
            foreach ($topCategories as $cat) {
                imagestring($im, 3, 40, $y, "- " . $cat->name . ": Rp " . number_format((float) $cat->total, 0, ',', '.'), $dark);
                $y += 20;
            }
        }

        // Save image to temp file
        $tempPath = tempnam(sys_get_temp_dir(), 'report_') . '.png';
        imagepng($im, $tempPath);
        imagedestroy($im);

        // Send to Telegram
        $success = $telegram->sendPhoto($chatId, $tempPath, "📊 Berikut adalah Laporan Keuangan Bulanan kamu dari MyPengeluaran!");

        // Delete temp file
        if (file_exists($tempPath)) {
            unlink($tempPath);
        }

        if ($success) {
            return Redirect::route('profile.index')
                ->with('status', 'telegram-report-sent');
        }

        return Redirect::route('profile.index')
            ->with('error', 'Gagal mengirim laporan ke Telegram. Coba lagi nanti.');
    }
}
