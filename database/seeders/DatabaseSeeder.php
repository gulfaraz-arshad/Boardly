<?php

namespace Database\Seeders;

use App\Models\Board;
use App\Models\BoardList;
use App\Models\Card;
use App\Models\CardActivity;
use App\Models\CardChecklist;
use App\Models\CardComment;
use App\Models\ChecklistItem;
use App\Models\Label;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ─────────────────────────────────────────────────────────
        // 1. USERS
        // ─────────────────────────────────────────────────────────

        $superAdmin = User::create([
            'name'              => 'Super Admin',
            'email'             => 'superadmin@example.com',
            'password'          => bcrypt('12345678'),
            'type'              => User::TYPE_SUPER_ADMIN,
            'email_verified_at' => now(),
        ]);

        $admin = User::create([
            'name'              => 'Admin User',
            'email'             => 'admin@example.com',
            'password'          => bcrypt('12345678'),
            'type'              => User::TYPE_ADMIN,
            'email_verified_at' => now(),
        ]);

        $alice = User::create([
            'name'              => 'Alice Chen',
            'email'             => 'alice@example.com',
            'password'          => bcrypt('12345678'),
            'type'              => User::TYPE_MEMBER,
            'email_verified_at' => now(),
        ]);

        $bob = User::create([
            'name'              => 'Bob Smith',
            'email'             => 'bob@example.com',
            'password'          => bcrypt('12345678'),
            'type'              => User::TYPE_MEMBER,
            'email_verified_at' => now(),
        ]);

        $carol = User::create([
            'name'              => 'Carol White',
            'email'             => 'carol@example.com',
            'password'          => bcrypt('12345678'),
            'type'              => User::TYPE_MEMBER,
            'email_verified_at' => now(),
        ]);

        $this->command->info('✓ Users created');

        // ─────────────────────────────────────────────────────────
        // 2. WORKSPACES
        // ─────────────────────────────────────────────────────────

        $engineeringWs = Workspace::create([
            'user_id'     => $admin->id,
            'name'        => 'Engineering',
            'description' => 'All engineering and development projects',
            'color'       => '#0c66e4',
        ]);

        $marketingWs = Workspace::create([
            'user_id'     => $alice->id,
            'name'        => 'Marketing',
            'description' => 'Marketing campaigns, content and growth',
            'color'       => '#943d73',
        ]);

        $designWs = Workspace::create([
            'user_id'     => $admin->id,
            'name'        => 'Design',
            'description' => 'UI/UX design and brand assets',
            'color'       => '#6544a3',
        ]);

        // ─────────────────────────────────────────────────────────
        // 3. WORKSPACE MEMBERSHIPS
        //    Tests: workspace role cascade to boards, viewer read-only
        // ─────────────────────────────────────────────────────────

        // Engineering: alice=admin, bob=member, carol=viewer
        $engineeringWs->members()->attach($alice->id, ['role' => 'admin',  'joined_at' => now()]);
        $engineeringWs->members()->attach($bob->id,   ['role' => 'member', 'joined_at' => now()]);
        $engineeringWs->members()->attach($carol->id, ['role' => 'viewer', 'joined_at' => now()]);

        // Marketing: bob=viewer, carol=member
        $marketingWs->members()->attach($bob->id,   ['role' => 'viewer', 'joined_at' => now()]);
        $marketingWs->members()->attach($carol->id, ['role' => 'member', 'joined_at' => now()]);

        // Design: bob=member, alice=admin
        $designWs->members()->attach($bob->id,   ['role' => 'member', 'joined_at' => now()]);
        $designWs->members()->attach($alice->id, ['role' => 'admin',  'joined_at' => now()]);

        // super_admin has no workspace rows — but bypasses all policies via before()
        // This lets us test that super_admin can access everything without being listed

        $this->command->info('✓ Workspaces + memberships created');

        // ─────────────────────────────────────────────────────────
        // 4. BOARDS
        // ─────────────────────────────────────────────────────────

        // ── Board 1: Product Development (Engineering) ───────────
        // Tests: explicit board roles override workspace roles
        $productBoard = $this->createBoard(
            owner: $admin,
            workspace: $engineeringWs,
            name: 'Product Development',
            description: 'Feature roadmap and sprint tracking',
            color: '#0c66e4',
            labels: [
                ['name' => 'Bug',      'color' => '#e2483d'],
                ['name' => 'Feature',  'color' => '#388bff'],
                ['name' => 'Urgent',   'color' => '#e97f33'],
                ['name' => 'Backend',  'color' => '#1f845a'],
                ['name' => 'Frontend', 'color' => '#8270db'],
                ['name' => 'Docs',     'color' => '#626f86'],
            ]
        );
        // Explicit board members (alice=admin, bob=member via workspace already, but explicit overrides)
        $productBoard->members()->attach($alice->id, ['role' => 'admin',  'joined_at' => now()]);
        $productBoard->members()->attach($bob->id,   ['role' => 'member', 'joined_at' => now()]);
        // carol inherits workspace viewer role — no explicit board row needed (tests cascade)

        // ── Board 2: API Platform (Engineering) ──────────────────
        // Tests: admin user (platform type) + workspace cascade for carol (viewer)
        $apiBoard = $this->createBoard(
            owner: $admin,
            workspace: $engineeringWs,
            name: 'API Platform',
            description: 'REST & GraphQL API development',
            color: '#216e4e',
            labels: [
                ['name' => 'Bug',          'color' => '#e2483d'],
                ['name' => 'Feature',      'color' => '#388bff'],
                ['name' => 'Breaking',     'color' => '#e97f33'],
                ['name' => 'Security',     'color' => '#943d73'],
            ]
        );
        $apiBoard->members()->attach($bob->id, ['role' => 'member', 'joined_at' => now()]);

        // ── Board 3: Marketing Q1 (Marketing) ────────────────────
        // Tests: alice owns this board, bob=viewer via workspace
        $marketingBoard = $this->createBoard(
            owner: $alice,
            workspace: $marketingWs,
            name: 'Marketing Campaign Q1',
            description: 'Q1 initiatives, campaigns and content',
            color: '#943d73',
            labels: [
                ['name' => 'Content',  'color' => '#ec4899'],
                ['name' => 'Ads',      'color' => '#f97316'],
                ['name' => 'Social',   'color' => '#06b6d4'],
                ['name' => 'Email',    'color' => '#8b5cf6'],
            ]
        );
        $marketingBoard->members()->attach($carol->id, ['role' => 'member', 'joined_at' => now()]);

        // ── Board 4: Design System (Design) ──────────────────────
        $designBoard = $this->createBoard(
            owner: $admin,
            workspace: $designWs,
            name: 'Design System',
            description: 'Component library and brand guidelines',
            color: '#6544a3',
            labels: [
                ['name' => 'Component', 'color' => '#8270db'],
                ['name' => 'Bug',       'color' => '#e2483d'],
                ['name' => 'Review',    'color' => '#e97f33'],
            ]
        );
        $designBoard->members()->attach($alice->id, ['role' => 'admin',  'joined_at' => now()]);
        $designBoard->members()->attach($bob->id,   ['role' => 'member', 'joined_at' => now()]);

        $this->command->info('✓ Boards created');

        // ─────────────────────────────────────────────────────────
        // 5. LISTS + CARDS — Product Development board
        // ─────────────────────────────────────────────────────────

        $pl = $productBoard->labels->keyBy('name');

        $backlog    = BoardList::create(['board_id' => $productBoard->id, 'name' => 'Backlog',     'position' => 0]);
        $inProgress = BoardList::create(['board_id' => $productBoard->id, 'name' => 'In Progress', 'position' => 1]);
        $review     = BoardList::create(['board_id' => $productBoard->id, 'name' => 'In Review',   'position' => 2]);
        $done       = BoardList::create(['board_id' => $productBoard->id, 'name' => 'Done',        'position' => 3]);

        // ── Cards ─────────────────────────────────────────────────

        // Backlog cards
        $c1 = $this->makeCard($admin, $backlog, 0, [
            'title'       => 'Design new onboarding flow',
            'description' => "Redesign the user onboarding experience with improved UX.\n\n**Goals:**\n- Reduce drop-off rate by 30%\n- Add progress indicators\n- Add interactive tutorial",
        ]);
        $c1->labels()->attach([$pl['Feature']->id, $pl['Frontend']->id]);
        $this->addActivity($c1, $admin, 'created', 'created this card');
        $this->addComment($c1, $alice, 'I have some wireframes ready — will share in the next standup.');
        $this->addActivity($c1, $alice, 'commented', 'added a comment');

        $c2 = $this->makeCard($admin, $backlog, 1, [
            'title'       => 'API rate limiting',
            'description' => 'Implement proper rate limiting for all public API endpoints using token bucket algorithm.',
        ]);
        $c2->labels()->attach([$pl['Backend']->id, $pl['Urgent']->id]);
        $this->addActivity($c2, $admin, 'created', 'created this card');
        $this->addChecklist($c2, 'Implementation Steps', [
            ['content' => 'Choose rate limiting library', 'is_checked' => true],
            ['content' => 'Implement per-IP limits',      'is_checked' => true],
            ['content' => 'Implement per-user limits',    'is_checked' => false],
            ['content' => 'Add Redis backend',            'is_checked' => false],
            ['content' => 'Write integration tests',      'is_checked' => false],
        ]);

        $c3 = $this->makeCard($alice, $backlog, 2, [
            'title' => 'Mobile responsive dashboard',
        ]);
        $c3->labels()->attach([$pl['Frontend']->id]);
        $this->addActivity($c3, $alice, 'created', 'created this card');

        // In Progress cards
        $c4 = $this->makeCard($alice, $inProgress, 0, [
            'title'       => 'User authentication refactor',
            'description' => 'Migrate from session-based auth to JWT with refresh token rotation.',
            'due_date'    => now()->addDays(3),
            'cover_color' => '#0c66e4',
        ]);
        $c4->labels()->attach([$pl['Feature']->id, $pl['Backend']->id]);
        $c4->members()->attach([$alice->id]);
        $this->addActivity($c4, $alice, 'created', 'created this card');
        $this->addActivity($c4, $admin, 'assigned', 'assigned **Alice Chen** to this card');
        $this->addComment($c4, $admin, 'Please make sure to test the refresh token rotation edge cases.');
        $this->addComment($c4, $alice, 'Will do — I\'ll add those to the checklist.');
        $this->addActivity($c4, $alice, 'commented', 'added a comment');
        $this->addChecklist($c4, 'Auth Checklist', [
            ['content' => 'Update login endpoint',               'is_checked' => true],
            ['content' => 'Implement JWT generation',            'is_checked' => true],
            ['content' => 'Implement refresh token rotation',    'is_checked' => false],
            ['content' => 'Update all middleware',               'is_checked' => false],
            ['content' => 'Revoke old sessions',                 'is_checked' => false],
        ]);

        $c5 = $this->makeCard($bob, $inProgress, 1, [
            'title'       => 'Database query optimization',
            'description' => 'Profile and optimize slow queries in the reporting module.',
            'due_date'    => now()->addDays(5),
        ]);
        $c5->labels()->attach([$pl['Backend']->id]);
        $c5->members()->attach([$bob->id]);
        $this->addActivity($c5, $bob, 'created', 'created this card');

        // In Review cards
        $c6 = $this->makeCard($bob, $review, 0, [
            'title'    => 'Search functionality',
            'due_date' => now()->subDay(), // overdue — tests red badge
        ]);
        $c6->labels()->attach([$pl['Feature']->id]);
        $this->addActivity($c6, $bob, 'created', 'created this card');
        $this->addActivity($c6, $bob, 'moved', 'moved this card from **In Progress** to **In Review**',
            ['from_list' => 'In Progress', 'to_list' => 'In Review']);
        $this->addComment($c6, $alice, 'Looks good! Just a few nits on the debounce logic.');

        // Done cards
        $c7 = $this->makeCard($admin, $done, 0, [
            'title'        => 'Project setup and scaffolding',
            'is_completed' => true,
        ]);
        $this->addActivity($c7, $admin, 'created', 'created this card');
        $this->addActivity($c7, $admin, 'completed', 'marked this card complete');

        $c8 = $this->makeCard($alice, $done, 1, [
            'title'        => 'CI/CD pipeline configuration',
            'description'  => 'Set up GitHub Actions for automated testing and deployment.',
            'is_completed' => true,
            'cover_color'  => '#216e4e',
        ]);
        $this->addActivity($c8, $alice, 'created', 'created this card');
        $this->addActivity($c8, $admin, 'completed', 'marked this card complete');

        $this->command->info('✓ Product Development board seeded');

        // ─────────────────────────────────────────────────────────
        // 6. API Platform board
        // ─────────────────────────────────────────────────────────

        $al  = $apiBoard->labels->keyBy('name');

        $todo    = BoardList::create(['board_id' => $apiBoard->id, 'name' => 'Todo',        'position' => 0]);
        $doing   = BoardList::create(['board_id' => $apiBoard->id, 'name' => 'In Progress', 'position' => 1]);
        $apidone = BoardList::create(['board_id' => $apiBoard->id, 'name' => 'Done',        'position' => 2]);

        $a1 = $this->makeCard($admin, $todo, 0, ['title' => 'OAuth2 scopes audit']);
        $a1->labels()->attach([$al['Security']->id]);
        $this->addActivity($a1, $admin, 'created', 'created this card');

        $a2 = $this->makeCard($bob, $todo, 1, ['title' => 'Pagination standards', 'description' => 'Standardise cursor-based pagination across all list endpoints.']);
        $a2->labels()->attach([$al['Feature']->id]);
        $this->addActivity($a2, $bob, 'created', 'created this card');

        $a3 = $this->makeCard($admin, $doing, 0, [
            'title'    => 'GraphQL schema v2',
            'due_date' => now()->addDays(7),
        ]);
        $a3->labels()->attach([$al['Breaking']->id, $al['Feature']->id]);
        $a3->members()->attach([$admin->id]);
        $this->addActivity($a3, $admin, 'created', 'created this card');
        $this->addChecklist($a3, 'Schema Changes', [
            ['content' => 'Audit existing resolvers',  'is_checked' => true],
            ['content' => 'Draft new schema',          'is_checked' => false],
            ['content' => 'Update documentation',      'is_checked' => false],
        ]);

        $a4 = $this->makeCard($admin, $apidone, 0, ['title' => 'Swagger/OpenAPI 3.0 docs', 'is_completed' => true]);
        $this->addActivity($a4, $admin, 'created', 'created this card');
        $this->addActivity($a4, $admin, 'completed', 'marked this card complete');

        $this->command->info('✓ API Platform board seeded');

        // ─────────────────────────────────────────────────────────
        // 7. Marketing Q1 board
        // ─────────────────────────────────────────────────────────

        $ml  = $marketingBoard->labels->keyBy('name');

        $ideas     = BoardList::create(['board_id' => $marketingBoard->id, 'name' => 'Ideas',     'position' => 0]);
        $planned   = BoardList::create(['board_id' => $marketingBoard->id, 'name' => 'Planned',   'position' => 1]);
        $executing = BoardList::create(['board_id' => $marketingBoard->id, 'name' => 'Executing', 'position' => 2]);
        $published = BoardList::create(['board_id' => $marketingBoard->id, 'name' => 'Published', 'position' => 3]);

        $m1 = $this->makeCard($alice, $ideas, 0, ['title' => 'Blog post series on AI trends']);
        $m1->labels()->attach([$ml['Content']->id]);
        $this->addActivity($m1, $alice, 'created', 'created this card');

        $m2 = $this->makeCard($alice, $ideas, 1, ['title' => 'Social media campaign for product launch']);
        $m2->labels()->attach([$ml['Social']->id, $ml['Ads']->id]);
        $this->addActivity($m2, $alice, 'created', 'created this card');

        $m3 = $this->makeCard($alice, $planned, 0, [
            'title'    => 'Email newsletter redesign',
            'due_date' => now()->addWeek(),
            'description' => 'Complete visual refresh of the monthly newsletter template.',
        ]);
        $m3->labels()->attach([$ml['Email']->id]);
        $this->addActivity($m3, $alice, 'created', 'created this card');
        $this->addComment($m3, $carol, 'I can handle the copywriting for this one.');

        $m4 = $this->makeCard($carol, $executing, 0, [
            'title'    => 'Conference sponsorship outreach',
            'due_date' => now()->addDays(2),
        ]);
        $m4->members()->attach([$carol->id]);
        $this->addActivity($m4, $carol, 'created', 'created this card');

        $m5 = $this->makeCard($alice, $published, 0, [
            'title'        => 'Product Hunt launch post',
            'is_completed' => true,
            'cover_color'  => '#943d73',
        ]);
        $this->addActivity($m5, $alice, 'created', 'created this card');
        $this->addActivity($m5, $alice, 'completed', 'marked this card complete');

        $this->command->info('✓ Marketing Q1 board seeded');

        // ─────────────────────────────────────────────────────────
        // 8. Design System board
        // ─────────────────────────────────────────────────────────

        $dl = $designBoard->labels->keyBy('name');

        $d_backlog = BoardList::create(['board_id' => $designBoard->id, 'name' => 'Backlog',     'position' => 0]);
        $d_design  = BoardList::create(['board_id' => $designBoard->id, 'name' => 'Designing',   'position' => 1]);
        $d_review  = BoardList::create(['board_id' => $designBoard->id, 'name' => 'In Review',   'position' => 2]);
        $d_done    = BoardList::create(['board_id' => $designBoard->id, 'name' => 'Shipped',     'position' => 3]);

        $d1 = $this->makeCard($alice, $d_backlog, 0, ['title' => 'Button component variants', 'description' => 'Primary, secondary, destructive, ghost sizes.']);
        $d1->labels()->attach([$dl['Component']->id]);
        $this->addActivity($d1, $alice, 'created', 'created this card');

        $d2 = $this->makeCard($bob, $d_backlog, 1, ['title' => 'Dark mode colour tokens']);
        $d2->labels()->attach([$dl['Component']->id]);
        $this->addActivity($d2, $bob, 'created', 'created this card');

        $d3 = $this->makeCard($alice, $d_design, 0, [
            'title'       => 'Navigation sidebar redesign',
            'description' => 'New collapsible sidebar with workspace switcher.',
            'due_date'    => now()->addDays(4),
            'cover_color' => '#6544a3',
        ]);
        $d3->labels()->attach([$dl['Component']->id, $dl['Review']->id]);
        $d3->members()->attach([$alice->id]);
        $this->addActivity($d3, $alice, 'created', 'created this card');
        $this->addComment($d3, $bob, 'Should we align this with the Figma file first?');
        $this->addChecklist($d3, 'Design Checklist', [
            ['content' => 'Desktop layout',  'is_checked' => true],
            ['content' => 'Mobile layout',   'is_checked' => false],
            ['content' => 'Accessibility',   'is_checked' => false],
            ['content' => 'Figma handoff',   'is_checked' => false],
        ]);

        $d4 = $this->makeCard($admin, $d_done, 0, ['title' => 'Typography scale', 'is_completed' => true]);
        $this->addActivity($d4, $admin, 'created', 'created this card');
        $this->addActivity($d4, $admin, 'completed', 'marked this card complete');

        $this->command->info('✓ Design System board seeded');

        // ─────────────────────────────────────────────────────────
        // Summary
        // ─────────────────────────────────────────────────────────
        $this->command->newLine();
        $this->command->table(
            ['Email', 'Password', 'Type', 'Role example'],
            [
                ['superadmin@example.com', 'password', 'super_admin', 'bypasses all policies'],
                ['admin@example.com',      'password', 'admin',       'owner of Engineering + Design workspaces'],
                ['alice@example.com',      'password', 'member',      'ws admin in Engineering, owns Marketing'],
                ['bob@example.com',        'password', 'member',      'ws member in Engineering, viewer in Marketing'],
                ['carol@example.com',      'password', 'member',      'ws viewer in Engineering, member in Marketing'],
            ]
        );
    }

    // ─────────────────────────────────────────────────────────────
    // Helpers — avoid depending on Actions that call auth()->user()
    // ─────────────────────────────────────────────────────────────

    private function createBoard(
        User      $owner,
        Workspace $workspace,
        string    $name,
        string    $description,
        string    $color,
        array     $labels = [],
    ): Board {
        $board = Board::create([
            'user_id'      => $owner->id,
            'workspace_id' => $workspace->id,
            'name'         => $name,
            'description'  => $description,
            'color'        => $color,
        ]);

        // Owner always gets an explicit board_members row
        $board->members()->attach($owner->id, ['role' => 'owner', 'joined_at' => now()]);

        foreach ($labels as $label) {
            $board->labels()->create($label);
        }

        return $board;
    }

    private function makeCard(User $creator, BoardList $list, int $position, array $data): Card
    {
        return Card::create([
            'board_list_id' => $list->id,
            'created_by'    => $creator->id,
            'title'         => $data['title'],
            'description'   => $data['description'] ?? null,
            'position'      => $position,
            'due_date'      => $data['due_date'] ?? null,
            'is_completed'  => $data['is_completed'] ?? false,
            'cover_color'   => $data['cover_color'] ?? null,
        ]);
    }

    private function addActivity(Card $card, User $user, string $type, string $content, array $metadata = []): void
    {
        CardActivity::create([
            'card_id'  => $card->id,
            'user_id'  => $user->id,
            'type'     => $type,
            'content'  => $content,
            'metadata' => empty($metadata) ? null : $metadata,
        ]);
    }

    private function addComment(Card $card, User $user, string $body): void
    {
        CardComment::create([
            'card_id' => $card->id,
            'user_id' => $user->id,
            'body'    => $body,
        ]);
    }

    private function addChecklist(Card $card, string $title, array $items): void
    {
        $checklist = CardChecklist::create([
            'card_id'  => $card->id,
            'title'    => $title,
            'position' => $card->checklists()->count(),
        ]);

        foreach ($items as $i => $item) {
            ChecklistItem::create([
                'card_checklist_id' => $checklist->id,
                'content'           => $item['content'],
                'is_checked'        => $item['is_checked'],
                'position'          => $i,
            ]);
        }
    }
}
