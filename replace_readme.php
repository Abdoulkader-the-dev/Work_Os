<?php
$file = __DIR__ . '/README.md';
$content = file_get_contents($file);

$replacements = [
    'Un workspace peut aussi être partagé' => 'Un espace de travail peut aussi être partagé',
    'rejoindre automatiquement le workspace.' => 'rejoindre automatiquement l\'espace de travail.',
    'dans un workspace.' => 'dans un espace de travail.',
    'qui gère le workspace.' => 'qui gère l\'espace de travail.',
    'renommer le workspace,' => 'renommer l\'espace de travail,',
    'supprimer le workspace,' => 'supprimer l\'espace de travail,',
    'supprimer le workspace.' => 'supprimer l\'espace de travail.',
    'supprimer les boards,' => 'supprimer les tableaux,',
    'contenu du workspace.' => 'contenu de l\'espace de travail.',
    'consulter le workspace,' => 'consulter l\'espace de travail,',
    'sur les boards et les tâches,' => 'sur les tableaux et les tâches,',
    'un workspace partagé.' => 'un espace de travail partagé.',
    'voir le workspace,' => 'voir l\'espace de travail,',
    'lire les boards, tâches,' => 'lire les tableaux, tâches,',
    'changer de workspace' => 'changer d\'espace de travail',
    'vues de board' => 'vues de tableau',
    'Le dashboard affiche' => 'Le tableau de bord affiche',
    'création de boards,' => 'création de tableaux,',
    'modification de boards,' => 'modification de tableaux,',
    'suppression de boards,' => 'suppression de tableaux,',
    'Les workspaces peuvent' => 'Les espaces de travail peuvent',
    'Le partage de workspace' => 'Le partage d\'espace de travail',
    'Le workspace courant' => 'L\'espace de travail courant',
    'Les boards sont rattachés au workspace' => 'Les tableaux sont rattachés à l\'espace de travail',
    'rattachement au workspace,' => 'rattachement à l\'espace de travail,',
    'rôles de workspace' => 'rôles d\'espace de travail',
    'membres de workspace,' => 'membres d\'espace de travail,',
    'chaque workspace.' => 'chaque espace de travail.',
    'partage de workspace' => 'partage d\'espace de travail',
    'les workspaces peuvent être' => 'les espaces de travail peuvent être',
    'nouveau workspace.' => 'nouvel espace de travail.',
    'utilisateur/workspace' => 'utilisateur/espace de travail'
];

$newContent = strtr($content, $replacements);
file_put_contents($file, $newContent);
echo "README updated.\n";
