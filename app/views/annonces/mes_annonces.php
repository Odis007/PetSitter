<?php require ROOT . '/app/views/layout/header.php'; ?>

<h2>Mes annonces</h2>
<p><a class="bouton" href="index.php?action=creer">+ Publier une annonce</a></p>

<!-- Statistiques du propriétaire -->
<div class="stats">
  <div class="stat">
    <strong><?= $stats['nbGardes'] ?></strong>
    <span>garde(s) confiée(s)</span>
  </div>
  <div class="stat">
    <strong><?= $stats['gardienPrefere'] ? e($stats['gardienPrefere']['nom']) : '—' ?></strong>
    <span>gardien le plus sollicité</span>
  </div>
</div>

<?php if (empty($annonces)): ?>
  <p class="vide">Vous n'avez pas encore publié d'annonce.</p>
<?php else: ?>
  <div class="grille">
    <?php foreach ($annonces as $a): ?>
      <div class="carte-annonce">
        <h3><?= e($a->titre) ?></h3>
        <p><?= e($a->ville) ?> · <?= e($a->animal) ?></p>
        <p class="dates">Du <?= Affichage::date($a->date_debut) ?> au <?= Affichage::date($a->date_fin) ?></p>

        <?= Affichage::badge($a->statut) ?>

        <a href="index.php?action=detail&id=<?= $a->id ?>">Voir le détail</a>
        <a href="index.php?action=recues&id=<?= $a->id ?>">
          Candidatures reçues (<?= $a->nb_candidatures ?>)
        </a>

        <?php if ($a->estModifiable()): ?>
          <div class="actions">
            <a href="index.php?action=modifier&id=<?= $a->id ?>" class="petit bouton">Modifier</a>
            <form method="post" action="index.php?action=supprimer&id=<?= $a->id ?>"
                  onsubmit="return confirm('Supprimer définitivement cette annonce ?');">
              <?= Securite::champ() ?>
              <button type="submit" class="petit danger">Supprimer</button>
            </form>
          </div>
        <?php endif; ?>

        <?php if ($a->statut === 'pourvue'): ?>
          <form method="post" action="index.php?action=terminer&id=<?= $a->id ?>">
            <?= Securite::champ() ?>
            <button type="submit" class="petit">Marquer la garde comme terminée</button>
          </form>
        <?php endif; ?>

        <?php if ($peutNoter[$a->id]): ?>
          <a class="bouton" href="index.php?action=noter&id=<?= $a->id ?>">Noter mon gardien</a>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<?php require ROOT . '/app/views/layout/footer.php'; ?>
