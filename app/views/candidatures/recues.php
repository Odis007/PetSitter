<?php require ROOT . '/app/views/layout/header.php'; ?>

<h2>Candidatures pour « <?= e($annonce->titre) ?> »</h2>
<p><?= Affichage::badge($annonce->statut) ?></p>

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
            <a href="index.php?action=profil&id=<?= $c->id_gardien ?>"><?= e($c->gardien) ?></a>
          </td>
          <td><?= e(Affichage::note($c->note_gardien)) ?></td>
          <td><?= nl2br(e($c->message)) ?></td>
          <td><?= Affichage::badge($c->statut) ?></td>
          <td>
            <?php if ($c->statut === 'en_attente' && $annonce->estOuverte()): ?>
              <div class="actions">
                <form method="post" action="index.php?action=repondre&id=<?= $c->id ?>">
                  <?= Securite::champ() ?>
                  <input type="hidden" name="decision" value="acceptee">
                  <button type="submit" class="petit">Accepter</button>
                </form>
                <form method="post" action="index.php?action=repondre&id=<?= $c->id ?>">
                  <?= Securite::champ() ?>
                  <input type="hidden" name="decision" value="refusee">
                  <button type="submit" class="petit danger">Refuser</button>
                </form>
              </div>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
    </table>
  </div>
<?php endif; ?>

<p><a href="index.php?action=detail&id=<?= $annonce->id ?>">← Retour à l'annonce</a></p>

<?php require ROOT . '/app/views/layout/footer.php'; ?>
