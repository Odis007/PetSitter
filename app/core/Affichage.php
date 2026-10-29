<?php
// app/core/Affichage.php : petites fonctions de mise en forme utilisées par les vues
// (aucune requête SQL ici : seulement du texte et du HTML)

class Affichage
{
    // Badge coloré d'un statut d'annonce ou de candidature
    public static function badge(string $statut): string
    {
        $libelle = match ($statut) {
            'ouverte'    => 'Ouverte',
            'pourvue'    => 'Pourvue',
            'terminee'   => 'Terminée',
            'en_attente' => 'En attente',
            'acceptee'   => 'Acceptée',
            'refusee'    => 'Refusée',
            default      => $statut,
        };
        return '<span class="badge badge--' . e($statut) . '">' . e($libelle) . '</span>';
    }

    // Emoji affiché à la place d'une photo, selon l'animal
    public static function emoji(string $animal): string
    {
        return match ($animal) {
            'Chien'   => '🐶',
            'Chat'    => '🐱',
            'Lapin'   => '🐰',
            'Rongeur' => '🐹',
            'Oiseau'  => '🐦',
            'Poisson' => '🐟',
            default   => '🐾',
        };
    }

    // Classe CSS du fond coloré de la carte : visuel--chien, visuel--chat...
    public static function classeVisuel(string $animal): string
    {
        $classe = strtolower($animal);
        return in_array($classe, ['chien', 'chat', 'lapin', 'rongeur', 'oiseau', 'poisson'], true)
            ? 'visuel--' . $classe
            : 'visuel--autre';
    }

    // Première lettre d'un nom (pour les ronds "avatar")
    public static function initiale(?string $nom): string
    {
        return e(mb_strtoupper(mb_substr($nom ?? '?', 0, 1)));
    }

    // 2026-10-22 -> 22/10/2026
    public static function date(?string $date): string
    {
        return $date ? date('d/m/Y', strtotime($date)) : '';
    }

    // Période lisible : "15 - 22 avril (8 jours)"
    public static function periode(string $debut, string $fin): string
    {
        $mois = ['', 'janvier', 'février', 'mars', 'avril', 'mai', 'juin', 'juillet',
                 'août', 'septembre', 'octobre', 'novembre', 'décembre'];
        $d = strtotime($debut);
        $f = strtotime($fin);
        $jours = (int) round(($f - $d) / 86400) + 1;   // 86400 secondes = 1 jour

        if ($d === $f) {
            $texte = date('j', $d) . ' ' . $mois[(int) date('n', $d)];
        } elseif (date('n', $d) === date('n', $f)) {
            $texte = date('j', $d) . ' - ' . date('j', $f) . ' ' . $mois[(int) date('n', $f)];
        } else {
            $texte = date('j', $d) . ' ' . $mois[(int) date('n', $d)] . ' - '
                   . date('j', $f) . ' ' . $mois[(int) date('n', $f)];
        }
        return $texte . ' (' . $jours . ($jours > 1 ? ' jours)' : ' jour)');
    }

    // Raccourcit un texte trop long
    public static function extrait(?string $texte, int $longueur = 60): string
    {
        return e(mb_strimwidth($texte ?? '', 0, $longueur, '…'));
    }

    // Note moyenne affichée, ou "Pas encore d'avis"
    public static function note(?float $note): string
    {
        if ($note === null) {
            return "Pas encore d'avis";
        }
        return '★ ' . number_format($note, 1, ',', '') . ' / 5';
    }
}
