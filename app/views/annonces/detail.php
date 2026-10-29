<?php require ROOT . '/app/views/layout/header.php'; ?>

<!-- Aucune règle métier ici : seulement des if sur les booléens calculés par le contrôleur -->
<h2><?= e($annonce->titre) ?> <?= Affichage::badge($annonce->statut) ?></h2>

<p class="infos">
  <?= Affichage::emoji($annonce->animal) ?> <?= e($annonce->animal) ?>
  · <?= e($annonce->ville) ?>
  · du <?= Affichage::date($annonce->date_debut) ?> au <?= Affichage::date($annonce->date_fin) ?>
</p>

<p>
  Propriétaire :
  <a href="index.php?action=profil&id=<?= $annonce->id_proprietaire ?>"><?= e($annonce->proprietaire) ?></a>
  (<?= e(Affichage::note($noteProprietaire)) ?>)
</p>

<!-- nl2br APRÈS e() : on échappe d'abord, puis on transforme les retours à la ligne -->
<p class="description"><?= nl2br(e($annonce->description)) ?></p>

<?php if ($estProprio): ?>
  <div class="actions">
    <?php if ($annonce->estModifiable()): ?>
      <a class="bouton bouton--clair" href="index.php?action=modifier&id=<?= $annonce->id ?>">Modifier</a>
    <?php endif; ?>

    <a class="bouton" href="index.php?action=recues&id=<?= $annonce->id ?>">
      Candidatures reçues (<?= $nbCandidatures ?>)
    </a>

    <?php if ($annonce->estModifiable()): ?>
      <form method="post" action="index.php?action=supprimer&id=<?= $annonce->id ?>"
            onsubmit="return confirm('Supprimer définitivement cette annonce ?');">
        <?= Securite::champ() ?>
        <button type="submit" class="danger">Supprimer</button>
      </form>
    <?php endif; ?>

    <?php if ($annonce->statut === 'pourvue'): ?>
      <form method="post" action="index.php?action=terminer&id=<?= $annonce->id ?>">
        <?= Securite::champ() ?>
        <button type="submit">Marquer la garde comme terminée</button>
      </form>
    <?php endif; ?>
  </div>
<?php endif; ?>

<?php if ($peutPostuler): ?>
  <h3>Postuler</h3>
  <form method="post" action="index.php?action=postuler&id=<?= $annonce->id ?>" class="formulaire">
    <?= Securite::champ() ?>
    <label>Votre message au propriétaire (10 à 500 caractères)</label>
    <textarea name="message" rows="4" maxlength="500" required
              placeholder="Présentez-vous en quelques mots"></textarea>
    <button type="submit">Envoyer ma candidature</button>
  </form>
<?php elseif ($aPostule): ?>
  <p class="info">Vous avez déjà postulé à cette annonce.
    <a href="index.php?action=mes_candidatures">Voir mes candidatures</a></p>
<?php elseif (!isset($_SESSION['user_id']) && $annonce->estOuverte()): ?>
  <p class="info"><a href="index.php?action=connexion">Connectez-vous</a> pour postuler.</p>
<?php elseif (!$estProprio && !$annonce->estOuverte()): ?>
  <p class="info">Cette annonce n'accepte plus de candidatures.</p>
<?php endif; ?>

<?php if ($peutNoter): ?>
  <p><a class="bouton" href="index.php?action=noter&id=<?= $annonce->id ?>">Laisser un avis</a></p>
<?php endif; ?>

<p><a href="index.php?action=liste">&larr; Retour aux annonces</a></p>

<?php require ROOT . '/app/views/layout/footer.php'; ?>
