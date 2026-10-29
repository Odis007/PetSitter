<?php require ROOT . '/app/views/layout/header.php'; ?>

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
            <a href="index.php?action=detail&id=<?= $c->id_annonce ?>"><?= e($c->annonce_titre) ?></a>
          </td>
          <td><?= Affichage::date($c->created_at) ?></td>
          <td><?= Affichage::badge($c->statut) ?></td>
          <td><?= Affichage::badge($c->annonce_statut ?? '') ?></td>
          <td>
            <?php if ($c->statut === 'en_attente'): ?>
              <form method="post" action="index.php?action=retirer&id=<?= $c->id ?>"
                    onsubmit="return confirm('Retirer cette candidature ?');">
                <?= Securite::champ() ?>
                <button type="submit" class="petit danger">Retirer</button>
              </form>
            <?php endif; ?>
            <?php if ($peutNoter[$c->id]): ?>
              <a class="bouton" href="index.php?action=noter&id=<?= $c->id_annonce ?>">Laisser un avis</a>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
    </table>
  </div>
<?php endif; ?>

<?php require ROOT . '/app/views/layout/footer.php'; ?>
