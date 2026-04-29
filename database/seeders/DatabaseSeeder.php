<?php

namespace Database\Seeders;

use App\Actions\CreateBoard;
use App\Actions\CreateCard;
use App\Models\BoardList;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * @throws \Exception
     */
    public function run(): void
    {
        // ─── Users ────────────────────────────────────────────────
        $admin = User::create([
            'name'     => 'Admin User',
            'email'    => 'admin@example.com',
            'password' => Hash::make('password'),
        ]);

        $alice = User::create([
            'name'     => 'Alice Chen',
            'email'    => 'alice@example.com',
            'password' => Hash::make('password'),
        ]);

        $bob = User::create([
            'name'     => 'Bob Smith',
            'email'    => 'bob@example.com',
            'password' => Hash::make('password'),
        ]);

        // ─── Boards ───────────────────────────────────────────────
        $createBoard = app(CreateBoard::class);
        $createCard  = app(CreateCard::class);

        // Board 1: Product Development
        $productBoard = $createBoard->handle($admin, [
            'name'        => 'Product Development',
            'description' => 'Feature roadmap and sprint tracking',
            'color'       => '#0ea5e9',
        ]);

        // Add Alice and Bob as members
        $productBoard->members()->attach($alice->id, ['role' => 'admin', 'joined_at' => now()]);
        $productBoard->members()->attach($bob->id, ['role' => 'member', 'joined_at' => now()]);

        // Lists
        $backlog = BoardList::create(['board_id' => $productBoard->id, 'name' => 'Backlog', 'position' => 0]);
        $inProgress = BoardList::create(['board_id' => $productBoard->id, 'name' => 'In Progress', 'position' => 1]);
        $review = BoardList::create(['board_id' => $productBoard->id, 'name' => 'In Review', 'position' => 2]);
        $done = BoardList::create(['board_id' => $productBoard->id, 'name' => 'Done', 'position' => 3]);

        // Cards in Backlog
        $card1 = $createCard->handle($admin, $backlog, ['title' => 'Design new onboarding flow', 'description' => 'Redesign the user onboarding experience with improved UX.']);
        $card2 = $createCard->handle($admin, $backlog, ['title' => 'API rate limiting', 'description' => 'Implement proper rate limiting for all public API endpoints.']);
        $card3 = $createCard->handle($alice, $backlog, ['title' => 'Mobile responsive dashboard']);

        // Cards in Progress
        $card4 = $createCard->handle($alice, $inProgress, ['title' => 'User authentication refactor', 'description' => 'Migrate to JWT-based authentication.', 'due_date' => now()->addDays(3)]);
        $card5 = $createCard->handle($bob, $inProgress, ['title' => 'Database query optimization']);

        // Cards in Review
        $card6 = $createCard->handle($bob, $review, ['title' => 'Search functionality', 'due_date' => now()->subDay()]);

        // Cards Done
        $card7 = $createCard->handle($admin, $done, ['title' => 'Project setup and scaffolding']);
        $card7->update(['is_completed' => true]);

        // Attach labels
        $labels = $productBoard->labels;
        $bugLabel     = $labels->where('name', 'Bug')->first();
        $featureLabel = $labels->where('name', 'Feature')->first();
        $urgentLabel  = $labels->where('name', 'Urgent')->first();
        $backendLabel = $labels->where('name', 'Backend')->first();

        $card1->labels()->attach([$featureLabel->id]);
        $card2->labels()->attach([$backendLabel->id, $urgentLabel->id]);
        $card4->labels()->attach([$featureLabel->id, $backendLabel->id]);
        $card5->labels()->attach([$backendLabel->id]);
        $card6->labels()->attach([$featureLabel->id]);

        // Assign members to cards
        $card4->members()->attach([$alice->id]);
        $card5->members()->attach([$bob->id]);

        // ─── Board 2: Marketing ───────────────────────────────────
        $marketingBoard = $createBoard->handle($alice, [
            'name'        => 'Marketing Campaign Q1',
            'description' => 'Q1 marketing initiatives and campaigns',
            'color'       => '#ec4899',
        ]);

        $ideas = BoardList::create(['board_id' => $marketingBoard->id, 'name' => 'Ideas', 'position' => 0]);
        $planned = BoardList::create(['board_id' => $marketingBoard->id, 'name' => 'Planned', 'position' => 1]);
        $executing = BoardList::create(['board_id' => $marketingBoard->id, 'name' => 'Executing', 'position' => 2]);

        $createCard->handle($alice, $ideas, ['title' => 'Blog post series on AI trends']);
        $createCard->handle($alice, $ideas, ['title' => 'Social media campaign for product launch']);
        $createCard->handle($alice, $planned, ['title' => 'Email newsletter redesign', 'due_date' => now()->addWeek()]);
        $createCard->handle($alice, $executing, ['title' => 'Conference sponsorship outreach']);
    }
}
