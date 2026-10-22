<h2>Mes candidatures</h2>

<?php if (empty($candidatures)): ?>
  <p class="vide">
    Vous n'avez postulé à aucune annonce.
    <a href="index.php?action=liste">Voir les annonces disponibles</a>
  </p>
<?php else: ?>
  <div class="tableau">
    <table>
      <tr>
        <th>Annonce</th>
        <th>Envoyée le</th>
        <th>Ma candidature</th>
        <th>La garde</th>
        <th></th>
      </tr>
      <?php foreach ($candidatures as $c): ?>
        <tr>
          <td>
            <a href="index.php?action=fiche&id=<?= $c->id_annonce ?>">
              <?= htmlspecialchars($c->titre_annonce) ?>
            </a>
          </td>
          <td><?= dateFr($c->date_candidature) ?></td>
          <td><?= badgeStatut($c->statut) ?></td>
          <td><?= badgeStatut($c->statut_annonce) ?></td>
          <td>
            <?php if ($c->statut === 'en_attente'): ?>
              <form method="post" action="index.php?action=retirer"
                    onsubmit="return confirm('Retirer cette candidature ?')">
                <input type="hidden" name="id_candidature" value="<?= $c->id ?>">
                <button type="submit" class="petit danger">Retirer</button>
              </form>
            <?php endif; ?>
            <?php if ($peutNoter[$c->id]): ?>
              <a class="bouton" href="index.php?action=noter&id_annonce=<?= $c->id_annonce ?>">
                Noter le propriétaire
              </a>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
    </table>
  </div>
<?php endif; ?>
