<!-- ========== 1. Grand bandeau ========== -->
<section class="hero">
  <div class="conteneur hero-grille">
    <div>
      <span class="pastille">● Plateforme associative de proximité</span>
      <h1>
        Trouvez le gardien idéal
        <span class="souligne">près de chez vous</span>
      </h1>
      <p class="hero-texte">
        Partez l'esprit tranquille. Confiez votre compagnon à des passionnés de votre quartier,
        ou devenez vous-même gardien et partagez des moments de complicité, en toute confiance.
      </p>

      <div class="hero-boutons">
        <a href="index.php?action=creer" class="btn btn--corail btn--grand">Je cherche une garde</a>
        <a href="index.php?action=liste" class="btn btn--contour btn--grand">Je veux garder</a>
      </div>

      <?php if ($nbAvis > 0): ?>
        <div class="preuve">
          <div class="avatars">
            <?php foreach ($derniersAvis as $av): ?>
              <span class="avatar"><?= initiale($av->nom_auteur) ?></span>
            <?php endforeach; ?>
          </div>
          <div>
            <span class="etoiles-or">★★★★★</span><br>
            <strong><?= $nbAvis ?> avis</strong> laissés par la communauté
          </div>
        </div>
      <?php endif; ?>
    </div>

    <div class="hero-visuel">
      <div class="hero-photo">🐶</div>
      <span class="hero-badge">✓ Profil vérifié</span>

      <?php if ($meilleurMembre): ?>
        <a href="index.php?action=profil&id=<?= $meilleurMembre->id ?>" class="hero-note">
          <span class="avatar"><?= initiale($meilleurMembre->nom) ?></span>
          <span>
            <small>Membre le mieux noté (<?= htmlspecialchars($meilleurMembre->nom) ?>)</small>
            <strong>Note : <?= number_format($meilleurMembre->moyenne, 1, ',', '') ?> <span class="etoiles-or">★</span></strong>
          </span>
        </a>
      <?php endif; ?>
    </div>
  </div>
</section>

<!-- ========== 2. Comment ça marche ? ========== -->
<section class="etapes" id="comment">
  <div class="conteneur">
    <p class="surtitre">Simple et sécurisé</p>
    <h2 class="titre-section">Comment ça marche ?</h2>
    <p class="sous-titre">Un système basé sur la confiance pour tous les passionnés d'animaux.</p>

    <div class="etapes-grille">
      <div class="etape">
        <div class="etape-icone">📝</div>
        <span class="etape-numero">Étape 1</span>
        <h3>Publier une annonce</h3>
        <p>Détaillez les besoins de votre animal, les dates et votre ville. C'est simple, rapide et gratuit.</p>
      </div>
      <div class="etape">
        <div class="etape-icone etape-icone--vert">🤝</div>
        <span class="etape-numero">Étape 2</span>
        <h3>Recevoir &amp; valider</h3>
        <p>Recevez des candidatures, consultez les profils et les notes des gardiens, puis faites votre choix.</p>
      </div>
      <div class="etape">
        <div class="etape-icone">⭐</div>
        <span class="etape-numero">Étape 3</span>
        <h3>Noter &amp; évaluer</h3>
        <p>Une fois la garde terminée, laissez un avis pour construire la réputation de confiance de la communauté.</p>
      </div>
    </div>
  </div>
</section>

<!-- ========== 3. Annonces récentes ========== -->
<section class="zone-creme">
  <div class="conteneur">
    <div class="entete-section">
      <div>
        <h2 class="titre-section titre-section--gauche">Annonces récentes</h2>
        <p class="sous-titre sous-titre--gauche">Ils cherchent un gardien près de chez vous en ce moment.</p>
      </div>
      <a href="index.php?action=liste" class="lien-fleche">Voir toutes les annonces →</a>
    </div>

    <div class="cartes-grille cartes-grille--3">
      <?php foreach ($annonces as $a): ?>
        <article class="carte">
          <div class="carte-visuel visuel--<?= strtolower($a->type_animal) ?>">
            <span class="carte-emoji"><?= emojiAnimal($a->type_animal) ?></span>
            <span class="carte-statut"><?= badgeStatut($a->statut) ?></span>
          </div>

          <div class="carte-corps">
            <h3><?= htmlspecialchars($a->titre) ?></h3>

            <div class="detail">
              <span class="detail-icone">📍</span>
              <div><small>Lieu</small><?= htmlspecialchars($a->ville) ?></div>
            </div>
            <div class="detail">
              <span class="detail-icone">📅</span>
              <div><small>Dates</small><?= periode($a->date_debut, $a->date_fin) ?></div>
            </div>
            <div class="detail">
              <span class="detail-icone"><?= emojiAnimal($a->type_animal) ?></span>
              <div><small>Animal</small><?= htmlspecialchars($a->type_animal) ?></div>
            </div>

            <div class="carte-pied">
              <?php if ($a->statut === 'ouverte'): ?>
                <span class="discret">
                  <?= $a->nb_candidatures > 0
                    ? $a->nb_candidatures . ' candidature(s)'
                    : 'Soyez le premier !' ?>
                </span>
                <a href="index.php?action=fiche&id=<?= $a->id ?>" class="btn btn--sombre btn--petit">Voir l'annonce</a>
              <?php else: ?>
                <span class="texte-vert">✓ Gardien trouvé !</span>
                <a href="index.php?action=fiche&id=<?= $a->id ?>" class="btn btn--gris btn--petit">Voir l'annonce</a>
              <?php endif; ?>
            </div>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>
