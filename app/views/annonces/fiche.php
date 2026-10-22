<h2><?= htmlspecialchars($annonce->titre) ?> <?= badgeStatut($annonce->statut) ?></h2>

<p class="infos">
  <?= htmlspecialchars($annonce->ville) ?> · <?= htmlspecialchars($annonce->type_animal) ?>
  · du <?= dateFr($annonce->date_debut) ?> au <?= dateFr($annonce->date_fin) ?>
</p>

<p>
  Publiée par
  <a href="index.php?action=profil&id=<?= $annonce->id_proprietaire ?>">
    <?= htmlspecialchars($annonce->nom_proprietaire) ?>
  </a>
  (<?= afficherNote($noteProprietaire) ?>)
</p>

<!-- nl2br garde les retours à la ligne de la description -->
<p class="description"><?= nl2br(htmlspecialchars($annonce->description ?? '')) ?></p>

<?php if ($estProprietaire): ?>
  <div class="actions">
    <a class="bouton" href="index.php?action=candidatures&id=<?= $annonce->id ?>">
      Voir les candidatures
    </a>
    <?php if ($annonce->statut === 'ouverte'): ?>
      <a class="bouton bouton--clair" href="index.php?action=modifier&id=<?= $annonce->id ?>">Modifier</a>
      <form method="post" action="index.php?action=supprimer"
            onsubmit="return confirm('Supprimer définitivement cette annonce ?')">
        <input type="hidden" name="id_annonce" value="<?= $annonce->id ?>">
        <button type="submit" class="danger">Supprimer</button>
      </form>
    <?php endif; ?>
  </div>

<?php elseif ($annonce->statut !== 'ouverte'): ?>
  <p class="info">Cette annonce n'accepte plus de candidatures.</p>

<?php elseif ($idConnecte === null): ?>
  <p class="info">
    <a href="index.php?action=connexion">Connectez-vous</a> pour postuler.
  </p>

<?php elseif ($dejaCandidat): ?>
  <p class="info">Vous avez déjà postulé à cette annonce.</p>

<?php else: ?>
  <form method="post" action="index.php?action=postuler" class="formulaire">
    <input type="hidden" name="id_annonce" value="<?= $annonce->id ?>">
    <label>Votre message de motivation</label>
    <textarea name="message" rows="4" required
              placeholder="Présentez-vous en quelques mots"></textarea>
    <button type="submit">Postuler</button>
  </form>
<?php endif; ?>
