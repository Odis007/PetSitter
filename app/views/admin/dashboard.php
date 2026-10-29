<?php require ROOT . '/app/views/layout/header.php'; ?>

<h2>Espace administrateur</h2>

<!-- Statistiques globales -->
<div class="stats">
  <div class="stat">
    <strong><?= $nbGardes ?></strong>
    <span>garde(s) réalisée(s)</span>
  </div>
  <div class="stat">
    <strong><?= e(Affichage::note($moyenne)) ?></strong>
    <span>note moyenne de la plateforme</span>
  </div>
  <div class="stat">
    <strong><?= $nbUtilisateurs ?></strong>
    <span>membre(s)</span>
  </div>
</div>

<!-- Modération des avis -->
<h3>Avis</h3>
<?php if (empty($avis)): ?>
  <p class="vide">Aucun avis.</p>
<?php else: ?>
  <div class="tableau">
    <table>
      <tr><th>Date</th><th>De</th><th>Pour</th><th>Note</th><th>Commentaire</th><th></th></tr>
      <?php foreach ($avis as $av): ?>
        <tr>
          <td><?= Affichage::date($av->date) ?></td>
          <td><?= e($av->auteur) ?></td>
          <td><?= e($av->cible) ?></td>
          <td><?= $av->note ?> / 5</td>
          <td><?= e($av->commentaire) ?></td>
          <td>
            <form method="post" action="index.php?action=supprimer_avis&id=<?= $av->id ?>"
                  onsubmit="return confirm('Supprimer cet avis ?');">
              <?= Securite::champ() ?>
              <button type="submit" class="petit danger">Supprimer</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
    </table>
  </div>
<?php endif; ?>

<!-- Gestion des comptes -->
<h3>Comptes</h3>
<div class="tableau">
  <table>
    <tr><th>Nom</th><th>Email</th><th>Rôle</th><th></th></tr>
    <?php foreach ($utilisateurs as $u): ?>
      <tr>
        <td><a href="index.php?action=profil&id=<?= $u->id ?>"><?= e($u->nom) ?></a></td>
        <td><?= e($u->email) ?></td>
        <td><?= e($u->role) ?></td>
        <td>
          <?php if ($u->id !== AuthController::utilisateurId()): ?>
            <form method="post" action="index.php?action=supprimer_utilisateur&id=<?= $u->id ?>"
                  onsubmit="return confirm('Supprimer ce compte et toutes ses données ?');">
              <?= Securite::champ() ?>
              <button type="submit" class="petit danger">Supprimer</button>
            </form>
          <?php endif; ?>
        </td>
      </tr>
    <?php endforeach; ?>
  </table>
</div>

<?php require ROOT . '/app/views/layout/footer.php'; ?>
