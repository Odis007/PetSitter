<h2>Profil de <?= htmlspecialchars($utilisateur->nom) ?></h2>

<div class="stats">
  <div class="stat">
    <strong><?= afficherNote($note) ?></strong>
    <span>note moyenne</span>
  </div>
  <div class="stat">
    <strong><?= $nbGardes ?></strong>
    <span>garde(s) réalisée(s)</span>
  </div>
</div>

<h3>Avis reçus</h3>

<?php if (empty($avis)): ?>
  <p class="vide">Aucun avis pour le moment.</p>
<?php endif; ?>

<?php foreach ($avis as $av): ?>
  <div class="avis">
    <p>
      <strong><?= str_repeat('★', $av->note) . str_repeat('☆', 5 - $av->note) ?></strong>
      par <?= htmlspecialchars($av->nom_auteur) ?>
      — <?= htmlspecialchars($av->titre_annonce) ?>, le <?= dateFr($av->date) ?>
    </p>
    <?php if ($av->commentaire): ?>
      <p><?= nl2br(htmlspecialchars($av->commentaire)) ?></p>
    <?php endif; ?>
  </div>
<?php endforeach; ?>
