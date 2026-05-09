<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Workspace;
use App\Models\Board;
use App\Models\Group;
use App\Models\Item;
use App\Models\Comment;
use App\Models\Meeting;
use App\Models\Notification;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ── USERS ──────────────────────────────────────────
        $caleb = User::create([
            'name'     => 'Caleb Messohounsounou',
            'email'    => 'caleb@unipod.com',
            'password' => Hash::make('password'),
        ]);

        $mohamed = User::create([
            'name'     => 'Mohamed AbdoulKader',
            'email'    => 'mohamed@unipod.com',
            'password' => Hash::make('password'),
        ]);

        $alice = User::create([
            'name'     => 'Alice Koné',
            'email'    => 'alice@unipod.com',
            'password' => Hash::make('password'),
        ]);

        $boris = User::create([
            'name'     => 'Boris Tchagninou',
            'email'    => 'boris@unipod.com',
            'password' => Hash::make('password'),
        ]);

        // ── WORKSPACE ──────────────────────────────────────
        $workspace = Workspace::create([
            'name'    => 'UniPod HQ',
            'color'   => '#0091CD',
            'user_id' => $caleb->id,
        ]);

        $workspace->members()->attach([
            $caleb->id   => ['role' => 'admin'],
            $mohamed->id => ['role' => 'admin'],
            $alice->id   => ['role' => 'member'],
            $boris->id   => ['role' => 'member'],
        ]);

        // ── BOARDS ─────────────────────────────────────────
        $boardBloc = Board::create([
            'name'         => 'Blochallenge 2025',
            'color'        => '#0091CD',
            'workspace_id' => $workspace->id,
        ]);

        $boardSite = Board::create([
            'name'         => 'Site Web UniPod',
            'color'        => '#FFD100',
            'workspace_id' => $workspace->id,
        ]);

        $boardAdmin = Board::create([
            'name'         => 'Administration',
            'color'        => '#22c55e',
            'workspace_id' => $workspace->id,
        ]);

        // ── GROUPS + ITEMS — Blochallenge ───────────────────
        $g1 = Group::create(['name' => 'Phase 1 — Cadrage', 'color' => '#0091CD', 'order' => 1, 'board_id' => $boardBloc->id]);
        $g2 = Group::create(['name' => 'Phase 2 — Développement', 'color' => '#FFD100', 'order' => 2, 'board_id' => $boardBloc->id]);
        $g3 = Group::create(['name' => 'Phase 3 — Livraison', 'color' => '#22c55e', 'order' => 3, 'board_id' => $boardBloc->id]);

        // Groupe 1
        $i1 = Item::create(['name' => 'Définir le brief du challenge', 'status' => 'done', 'priority' => 'haute', 'deadline' => '2025-06-01', 'deliverable' => 'Brief PDF envoyé', 'order' => 1, 'group_id' => $g1->id]);
        $i2 = Item::create(['name' => 'Contacter les partenaires', 'status' => 'done', 'priority' => 'haute', 'deadline' => '2025-06-10', 'deliverable' => 'MoU signé x3', 'order' => 2, 'group_id' => $g1->id]);
        $i3 = Item::create(['name' => 'Préparer le dossier de presse', 'status' => 'progress', 'priority' => 'moyenne', 'deadline' => '2025-06-20', 'order' => 3, 'group_id' => $g1->id]);
        $i4 = Item::create(['name' => 'Valider le budget prévisionnel', 'status' => 'blocked', 'priority' => 'critique', 'deadline' => '2025-06-15', 'obstacles' => 'En attente de validation du trésorier', 'order' => 4, 'group_id' => $g1->id]);

        // Groupe 2
        $i5 = Item::create(['name' => 'Créer le site web du challenge', 'status' => 'progress', 'priority' => 'haute', 'deadline' => '2025-07-01', 'order' => 1, 'group_id' => $g2->id]);
        $i6 = Item::create(['name' => 'Préparer le kit candidat', 'status' => 'todo', 'priority' => 'moyenne', 'deadline' => '2025-07-10', 'order' => 2, 'group_id' => $g2->id]);
        $i7 = Item::create(['name' => 'Organiser les sessions de pitchs', 'status' => 'todo', 'priority' => 'haute', 'deadline' => '2025-07-20', 'order' => 3, 'group_id' => $g2->id]);

        // Groupe 3
        $i8 = Item::create(['name' => 'Cérémonie de remise des prix', 'status' => 'todo', 'priority' => 'critique', 'deadline' => '2025-08-15', 'order' => 1, 'group_id' => $g3->id]);
        $i9 = Item::create(['name' => 'Rapport final Blochallenge', 'status' => 'todo', 'priority' => 'moyenne', 'deadline' => '2025-08-30', 'order' => 2, 'group_id' => $g3->id]);

        // ── GROUPS + ITEMS — Site Web ───────────────────────
        $g4 = Group::create(['name' => 'Design', 'color' => '#8b5cf6', 'order' => 1, 'board_id' => $boardSite->id]);
        $g5 = Group::create(['name' => 'Développement', 'color' => '#0091CD', 'order' => 2, 'board_id' => $boardSite->id]);

        $i10 = Item::create(['name' => 'Créer la charte graphique', 'status' => 'done', 'priority' => 'haute', 'deadline' => '2025-05-01', 'deliverable' => 'Figma validé', 'order' => 1, 'group_id' => $g4->id]);
        $i11 = Item::create(['name' => 'Wireframes des pages principales', 'status' => 'done', 'priority' => 'haute', 'deadline' => '2025-05-10', 'order' => 2, 'group_id' => $g4->id]);
        $i12 = Item::create(['name' => 'Développer la landing page', 'status' => 'progress', 'priority' => 'critique', 'deadline' => '2025-05-25', 'order' => 1, 'group_id' => $g5->id]);
        $i13 = Item::create(['name' => 'Intégrer le CMS', 'status' => 'todo', 'priority' => 'moyenne', 'deadline' => '2025-06-05', 'order' => 2, 'group_id' => $g5->id]);
        $i14 = Item::create(['name' => 'SEO et optimisation', 'status' => 'todo', 'priority' => 'basse', 'deadline' => '2025-06-15', 'order' => 3, 'group_id' => $g5->id]);

        // ── GROUPS + ITEMS — Administration ─────────────────
        $g6 = Group::create(['name' => 'Administratif', 'color' => '#22c55e', 'order' => 1, 'board_id' => $boardAdmin->id]);

        $i15 = Item::create(['name' => 'Renouveler les statuts de l\'association', 'status' => 'ongoing', 'priority' => 'haute', 'deadline' => '2025-12-31', 'order' => 1, 'group_id' => $g6->id]);
        $i16 = Item::create(['name' => 'Rapport d\'activité annuel', 'status' => 'todo', 'priority' => 'moyenne', 'deadline' => '2025-12-01', 'order' => 2, 'group_id' => $g6->id]);

        // ── ASSIGNATIONS ───────────────────────────────────
        $i1->assignees()->attach([$caleb->id, $mohamed->id]);
        $i2->assignees()->attach([$mohamed->id]);
        $i3->assignees()->attach([$alice->id]);
        $i4->assignees()->attach([$caleb->id, $boris->id]);
        $i5->assignees()->attach([$caleb->id]);
        $i6->assignees()->attach([$alice->id, $boris->id]);
        $i7->assignees()->attach([$mohamed->id]);
        $i8->assignees()->attach([$caleb->id, $mohamed->id, $alice->id]);
        $i10->assignees()->attach([$alice->id]);
        $i11->assignees()->attach([$alice->id, $caleb->id]);
        $i12->assignees()->attach([$caleb->id]);
        $i15->assignees()->attach([$mohamed->id]);

        // ── COMMENTS ───────────────────────────────────────
        Comment::create(['body' => 'Brief validé en réunion hier, on peut avancer.', 'item_id' => $i1->id, 'user_id' => $caleb->id]);
        Comment::create(['body' => '@caleb super, j\'envoie les MoU cette semaine.', 'item_id' => $i2->id, 'user_id' => $mohamed->id]);
        Comment::create(['body' => 'Le trésorier est en déplacement jusqu\'au 20. On attend.', 'item_id' => $i4->id, 'user_id' => $boris->id]);
        Comment::create(['body' => 'J\'ai commencé les wireframes sur Figma, lien partagé en DM.', 'item_id' => $i11->id, 'user_id' => $alice->id]);
        Comment::create(['body' => '@alice parfait, je regarde ça ce soir.', 'item_id' => $i11->id, 'user_id' => $caleb->id]);

        // ── MEETINGS ───────────────────────────────────────
        Meeting::create([
            'title'           => 'Réunion de cadrage Blochallenge',
            'date'            => '2025-06-02',
            'user_id'         => $caleb->id,
            'attendees'       => ['Caleb', 'Mohamed', 'Alice', 'Boris'],
            'bilan'           => [
                'Brief validé et partagé à l\'équipe',
                'Identification des 5 partenaires cibles',
                'Budget prévisionnel en cours de validation',
            ],
            'recommendations' => [
                'Accélérer la prise de contact partenaires',
                'Planifier une réunion hebdo de suivi',
            ],
            'actions'         => [
                ['text' => 'Envoyer les MoU aux partenaires', 'assignee_id' => $mohamed->id, 'deadline' => '2025-06-10', 'converted' => true, 'item_id' => $i2->id],
                ['text' => 'Finaliser le dossier de presse', 'assignee_id' => $alice->id, 'deadline' => '2025-06-20', 'converted' => true, 'item_id' => $i3->id],
                ['text' => 'Relancer le trésorier pour le budget', 'assignee_id' => $caleb->id, 'deadline' => '2025-06-12', 'converted' => false, 'item_id' => null],
            ],
        ]);

        Meeting::create([
            'title'           => 'Point hebdo Site Web',
            'date'            => '2025-05-12',
            'user_id'         => $caleb->id,
            'attendees'       => ['Caleb', 'Alice'],
            'bilan'           => [
                'Charte graphique validée',
                'Wireframes en cours de finalisation',
            ],
            'recommendations' => [
                'Commencer le dev frontend dès validation des wireframes',
            ],
            'actions'         => [
                ['text' => 'Finaliser les wireframes Figma', 'assignee_id' => $alice->id, 'deadline' => '2025-05-15', 'converted' => true, 'item_id' => $i11->id],
                ['text' => 'Démarrer la landing page', 'assignee_id' => $caleb->id, 'deadline' => '2025-05-20', 'converted' => true, 'item_id' => $i12->id],
            ],
        ]);

        // ── NOTIFICATIONS ──────────────────────────────────
        Notification::create([
            'type'         => 'mention',
            'message'      => '<strong>Mohamed</strong> vous a mentionné dans <strong>Contacter les partenaires</strong>',
            'action_url'   => '/boards/' . $boardBloc->id,
            'action_label' => 'Voir la tâche',
            'user_id'      => $caleb->id,
        ]);

        Notification::create([
            'type'         => 'status',
            'message'      => '<strong>Alice</strong> a changé le statut de <strong>Dossier de presse</strong> en <span style="color:#0091CD">En cours</span>',
            'action_url'   => '/boards/' . $boardBloc->id,
            'action_label' => 'Voir',
            'user_id'      => $caleb->id,
        ]);

        Notification::create([
            'type'         => 'deadline',
            'message'      => 'La tâche <strong>Valider le budget</strong> arrive à échéance dans 2 jours',
            'action_url'   => '/boards/' . $boardBloc->id,
            'action_label' => 'Voir la tâche',
            'user_id'      => $caleb->id,
        ]);

        Notification::create([
            'type'         => 'assignment',
            'message'      => '<strong>Caleb</strong> vous a assigné la tâche <strong>Créer le site web du challenge</strong>',
            'action_url'   => '/boards/' . $boardSite->id,
            'action_label' => 'Voir',
            'user_id'      => $mohamed->id,
            'read_at'      => now(),
        ]);

        Notification::create([
            'type'         => 'comment',
            'message'      => '<strong>Alice</strong> a commenté <strong>Wireframes des pages principales</strong>',
            'action_url'   => '/boards/' . $boardSite->id,
            'action_label' => 'Voir le commentaire',
            'user_id'      => $caleb->id,
            'read_at'      => now(),
        ]);
    }
}