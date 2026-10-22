<?php
// Filtres actuellement choisis (pour cocher les bonnes cases)
$urgent = !empty($_GET['urgent']);
$tri = $_GET['tri'] ?? 'proches';
$typeChoisi = $_GET['type_animal'] ?? '';
$ville = trim($_GET['ville'] ?? '');
?>

<!-- Un seul formulaire pour la barre de recherche ET les filtres de gauche -->
<form method="get" action="index.php">
  <input type="hidden" name="action" value="rechercher">

  <!-- ========== Bandeau + recherche ========== -->
  <section class="hero-annonces">
    <div class="conteneur">
      <h1>Trouvez le compagnon idéal à garder</h1>
      <p class="sous-titre">
        Parcourez les annonces près de chez vous. Utilisez les filtres pour trouver
        une garde qui correspond à vos disponibilités.
      </p>

      <?php require __DIR__ . '/recherche.php'; ?>

      <!-- Raccourcis -->
      <div class="puces">
        <a href="index.php?action=rechercher&urgent=1"
           class="puce <?= $urgent ? 'puce--active' : '' ?>">Urgences (-48h)</a>
        <a href="index.php?action=rechercher&type_animal=Chien"
           class="puce <?= $typeChoisi === 'Chien' ? 'puce--active' : '' ?>">🐶 Chiens</a>
        <a href="index.php?action=rechercher&type_animal=Chat"
           class="puce <?= $typeChoisi === 'Chat' ? 'puce--active' : '' ?>">🐱 Chats</a>
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
          <input type="checkbox" name="urgent" value="1" <?= $urgent ? 'checked' : '' ?>>
          Commence dans moins de 48h
        </label>

        <h4>Trier par</h4>
        <label class="case">
          <input type="radio" name="tri" value="proches" <?= $tri !== 'recentes' ? 'checked' : '' ?>>
          Date de garde la plus proche
        </label>
        <label class="case">
          <input type="radio" name="tri" value="recentes" <?= $tri === 'recentes' ? 'checked' : '' ?>>
          Annonces les plus récentes
        </label>

        <button type="submit" class="btn btn--sombre btn--bloc">Appliquer</button>
      </aside>

      <div>
        <p class="resultats">
          <strong><?= count($annonces) ?> annonce(s)</strong> trouvée(s)
          <?= $ville !== '' ? 'près de ' . htmlspecialchars($ville) : '' ?>
        </p>

        <?php if (empty($annonces)): ?>
          <div class="vide-boite">
            <p>😿 Aucune annonce ne correspond à votre recherche.</p>
            <a href="index.php?action=liste" class="btn btn--contour">Voir toutes les annonces</a>
          </div>
        <?php endif; ?>

        <div class="cartes-grille">
          <?php foreach ($annonces as $a): ?>
            <?php $urgente = $a->estUrgente(); ?>
            <article class="carte <?= $urgente ? 'carte--urgente' : '' ?>">
              <div class="carte-visuel visuel--<?= strtolower($a->type_animal) ?>">
                <span class="carte-emoji"><?= emojiAnimal($a->type_animal) ?></span>
                <?php if ($urgente): ?>
                  <span class="etiquette etiquette--urgence">Urgence (-48h)</span>
                <?php else: ?>
                  <span class="etiquette">📍 <?= htmlspecialchars($a->ville) ?></span>
                <?php endif; ?>
                <span class="carte-statut"><?= badgeStatut($a->statut) ?></span>
              </div>

              <div class="carte-corps">
                <div class="carte-titre">
                  <h3><?= htmlspecialchars($a->titre) ?></h3>
                  <span class="avatar" title="Publiée par <?= htmlspecialchars($a->nom_proprietaire) ?>">
                    <?= initiale($a->nom_proprietaire) ?>
                  </span>
                </div>

                <ul class="liste-points">
                  <li class="<?= $urgente ? 'point-rouge' : 'point-corail' ?>">
                    <?= periode($a->date_debut, $a->date_fin) ?>
                  </li>
                  <li><?= htmlspecialchars($a->type_animal) ?> · <?= htmlspecialchars($a->ville) ?></li>
                  <li><?= htmlspecialchars(extrait($a->description)) ?></li>
                </ul>

                <a href="index.php?action=fiche&id=<?= $a->id ?>"
                   class="btn btn--bloc <?= $urgente ? 'btn--rouge' : 'btn--sombre' ?>">
                  <?= $urgente ? 'Sauver la situation !' : 'Proposer mes services' ?>
                </a>
              </div>
            </article>
          <?php endforeach; ?>
        </div>
      </div>

    </div>
  </section>
</form>
