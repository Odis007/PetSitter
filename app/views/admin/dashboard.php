<h2>Espace administrateur</h2>

<!-- Statistiques globales -->
<div class="stats">
  <div class="stat">
    <strong><?= $nbGardes ?></strong>
    <span>garde(s) réalisée(s)</span>
  </div>
  <div class="stat">
    <strong><?= afficherNote($moyenne) ?></strong>
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
          <td><?= dateFr($av->date) ?></td>
          <td><?= htmlspecialchars($av->nom_auteur) ?></td>
          <td><?= htmlspecialchars($av->nom_cible) ?></td>
          <td><?= $av->note ?> / 5</td>
          <td><?= htmlspecialchars($av->commentaire ?? '') ?></td>
          <td>
            <form method="post" action="index.php?action=supprimerAvis">
              <input type="hidden" name="id" value="<?= $av->id ?>">
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
        <td>
          <a href="index.php?action=profil&id=<?= $u->id ?>"><?= htmlspecialchars($u->nom) ?></a>
        </td>
        <td><?= htmlspecialchars($u->email) ?></td>
        <td><?= $u->role ?></td>
        <td>
          <?php if ($u->id !== $_SESSION['user_id']): ?>
            <form method="post" action="index.php?action=supprimerUtilisateur">
              <input type="hidden" name="id" value="<?= $u->id ?>">
              <button type="submit" class="petit danger">Supprimer</button>
            </form>
          <?php endif; ?>
        </td>
      </tr>
    <?php endforeach; ?>
  </table>
</div>
