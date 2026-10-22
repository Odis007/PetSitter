<h2>Mes annonces</h2>

<!-- Statistiques du propriétaire -->
<div class="stats">
  <div class="stat">
    <strong><?= $stats['nbGardes'] ?></strong>
    <span>garde(s) confiée(s)</span>
  </div>
  <div class="stat">
    <strong>
      <?= $stats['gardienPrefere']
        ? htmlspecialchars($stats['gardienPrefere']->nom)
        : '—' ?>
    </strong>
    <span>gardien le plus sollicité</span>
  </div>
</div>

<?php if (empty($annonces)): ?>
  <p class="vide">
    Vous n'avez publié aucune annonce.
    <a href="index.php?action=creer">Publier ma première annonce</a>
  </p>
<?php endif; ?>

<div class="grille">
  <?php foreach ($annonces as $a): ?>
    <div class="carte-annonce">
      <h3><?= htmlspecialchars($a->titre) ?></h3>
      <p><?= htmlspecialchars($a->ville) ?>
         · <?= htmlspecialchars($a->type_animal) ?></p>
      <p class="dates">Du <?= dateFr($a->date_debut) ?> au <?= dateFr($a->date_fin) ?></p>

      <?= badgeStatut($a->statut) ?>

      <a href="index.php?action=fiche&id=<?= $a->id ?>">Voir le détail</a>
      <a href="index.php?action=candidatures&id=<?= $a->id ?>">Voir les candidatures</a>

      <?php if ($a->statut === 'ouverte'): ?>
        <div class="actions">
          <a href="index.php?action=modifier&id=<?= $a->id ?>" class="petit bouton">Modifier</a>
          <!-- confirm() affiche "OK / Annuler" : si on clique sur Annuler, rien n'est envoyé -->
          <form method="post" action="index.php?action=supprimer"
                onsubmit="return confirm('Supprimer définitivement cette annonce ?')">
            <input type="hidden" name="id_annonce" value="<?= $a->id ?>">
            <button type="submit" class="petit danger">Supprimer</button>
          </form>
        </div>
      <?php endif; ?>

      <?php if ($a->statut === 'pourvue'): ?>
        <form method="post" action="index.php?action=terminer">
          <input type="hidden" name="id_annonce" value="<?= $a->id ?>">
          <button type="submit" class="petit">Marquer la garde comme terminée</button>
        </form>
      <?php endif; ?>

      <?php if ($peutNoter[$a->id]): ?>
        <a class="bouton" href="index.php?action=noter&id_annonce=<?= $a->id ?>">
          Noter mon gardien
        </a>
      <?php endif; ?>
    </div>
  <?php endforeach; ?>
</div>
