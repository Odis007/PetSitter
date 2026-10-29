<!-- Barre de recherche : ville, date, animal (incluse dans liste.php) -->
<div class="barre-recherche">
  <label class="champ">
    <span>📍</span>
    <input type="text" name="ville" placeholder="Ville" value="<?= e($filtreVille) ?>">
  </label>

  <label class="champ" title="Gardes qui commencent à partir de cette date">
    <span>📅</span>
    <input type="date" name="date" value="<?= e($filtreDate) ?>">
  </label>

  <label class="champ">
    <span>🐾</span>
    <select name="animal">
      <option value="">Tous les animaux</option>
      <?php foreach (Annonce::ANIMAUX as $type): ?>
        <option value="<?= e($type) ?>" <?= $filtreAnimal === $type ? 'selected' : '' ?>>
          <?= e($type) ?>
        </option>
      <?php endforeach; ?>
    </select>
  </label>

  <button type="submit" class="btn btn--corail">🔍 Rechercher</button>
</div>
