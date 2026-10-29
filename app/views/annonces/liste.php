<?php require ROOT . '/app/views/layout/header.php'; ?>

<!-- Un seul formulaire GET pour la barre de recherche ET les filtres de gauche -->
<form method="get" action="index.php">
  <!-- Indispensable : sans lui, le formulaire GET perd l'action et le routeur retombe sur default -->
  <input type="hidden" name="action" value="liste">

  <!-- ========== Bandeau + recherche ========== -->
  <section class="hero-annonces">
    <div class="conteneur">
      <h1>Trouvez le compagnon idéal à garder</h1>
      <p class="sous-titre">
        Parcourez les annonces près de chez vous. Utilisez les filtres pour trouver
        une garde qui correspond à vos disponibilités.
      </p>

      <?php require ROOT . '/app/views/annonces/recherche.php'; ?>

      <!-- Raccourcis -->
      <div class="puces">
        <a href="index.php?action=liste&urgent=1"
           class="puce <?= $filtreUrgent ? 'puce--active' : '' ?>">Urgences (-48h)</a>
        <a href="index.php?action=liste&animal=Chien"
           class="puce <?= $filtreAnimal === 'Chien' ? 'puce--active' : '' ?>">🐶 Chiens</a>
        <a href="index.php?action=liste&animal=Chat"
           class="puce <?= $filtreAnimal === 'Chat' ? 'puce--active' : '' ?>">🐱 Chats</a>
      </div>
    </div>
  </section>

  <!-- ========== Filtres + résultats ========== -->
  <section class="zone-creme">
    <div class="conteneur annonces-mise-en-page">

      <aside class="filtres">
        <div class="filtres-entete">
          <h3>Filtres</h3>
          <a href="index.php?action=liste">Réinitialiser</a>
        </div>

        <h4>Urgence</h4>
        <label class="case">
          <input type="checkbox" name="urgent" value="1" <?= $filtreUrgent ? 'checked' : '' ?>>
          Commence dans moins de 48h
        </label>

        <h4>Trier par</h4>
        <label class="case">
          <input type="radio" name="tri" value="proches" <?= $filtreTri === 'proches' ? 'checked' : '' ?>>
          Date de garde la plus proche
        </label>
        <label class="case">
          <input type="radio" name="tri" value="recentes" <?= $filtreTri === 'recentes' ? 'checked' : '' ?>>
          Annonces les plus récentes
        </label>

        <button type="submit" class="btn btn--sombre btn--bloc">Appliquer</button>
      </aside>

      <div>
        <p class="resultats">
          <strong><?= count($annonces) ?> annonce(s)</strong> trouvée(s)
          <?= $filtreVille !== '' ? 'près de ' . e($filtreVille) : '' ?>
        </p>

        <?php if (empty($annonces)): ?>
          <div class="vide-boite">
            <p>😿 Aucune annonce ne correspond à votre recherche.</p>
            <a href="index.php?action=liste" class="btn btn--contour">Voir toutes les annonces</a>
          </div>
        <?php else: ?>
          <div class="cartes-grille">
            <?php foreach ($annonces as $a): ?>
              <?php $urgente = $a->estUrgente(); ?>
              <article class="carte <?= $urgente ? 'carte--urgente' : '' ?>">
                <div class="carte-visuel <?= Affichage::classeVisuel($a->animal) ?>">
                  <span class="carte-emoji"><?= Affichage::emoji($a->animal) ?></span>
                  <?php if ($urgente): ?>
                    <span class="etiquette etiquette--urgence">Urgence (-48h)</span>
                  <?php else: ?>
                    <span class="etiquette">📍 <?= e($a->ville) ?></span>
                  <?php endif; ?>
                  <span class="carte-statut"><?= Affichage::badge($a->statut) ?></span>
                </div>

                <div class="carte-corps">
                  <div class="carte-titre">
                    <h3><?= e($a->titre) ?></h3>
                    <span class="avatar" title="Publiée par <?= e($a->proprietaire) ?>">
                      <?= Affichage::initiale($a->proprietaire) ?>
                    </span>
                  </div>

                  <ul class="liste-points">
                    <li class="<?= $urgente ? 'point-rouge' : 'point-corail' ?>">
                      <?= e(Affichage::periode($a->date_debut, $a->date_fin)) ?>
                    </li>
                    <li><?= e($a->animal) ?> · <?= e($a->ville) ?> · par <?= e($a->proprietaire) ?></li>
                    <li><?= Affichage::extrait($a->description) ?></li>
                  </ul>

                  <a href="index.php?action=detail&id=<?= $a->id ?>"
                     class="btn btn--bloc <?= $urgente ? 'btn--rouge' : 'btn--sombre' ?>">
                    <?= $urgente ? 'Sauver la situation !' : 'Proposer mes services' ?>
                  </a>
                </div>
              </article>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>

    </div>
  </section>
</form>

<?php require ROOT . '/app/views/layout/footer.php'; ?>
