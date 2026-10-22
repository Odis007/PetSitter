<h2>Candidatures pour « <?= htmlspecialchars($annonce->titre) ?> »</h2>
<p><?= badgeStatut($annonce->statut) ?></p>

<?php if (empty($candidatures)): ?>
  <p class="vide">Personne n'a encore postulé.</p>
<?php else: ?>
  <div class="tableau">
    <table>
      <tr>
        <th>Gardien</th>
        <th>Note moyenne</th>
        <th>Message</th>
        <th>Statut</th>
        <th></th>
      </tr>
      <?php foreach ($candidatures as $c): ?>
        <tr>
          <td>
            <a href="index.php?action=profil&id=<?= $c->id_gardien ?>">
              <?= htmlspecialchars($c->nom_gardien) ?>
            </a>
          </td>
          <td><?= afficherNote($c->note_gardien) ?></td>
          <td><?= nl2br(htmlspecialchars($c->message ?? '')) ?></td>
          <td><?= badgeStatut($c->statut) ?></td>
          <td>
            <?php if ($annonce->statut === 'ouverte'): ?>
              <form method="post" action="index.php?action=valider">
                <input type="hidden" name="id_candidature" value="<?= $c->id ?>">
                <button type="submit" class="petit">Choisir ce gardien</button>
              </form>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
    </table>
  </div>
<?php endif; ?>

<p><a href="index.php?action=mesAnnonces">← Retour à mes annonces</a></p>
