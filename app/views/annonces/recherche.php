<!-- Barre de recherche : ville, date, type d'animal -->
<div class="barre-recherche">
  <label class="champ">
    <span>📍</span>
    <input type="text" name="ville" placeholder="Ville"
           value="<?= htmlspecialchars($_GET['ville'] ?? '') ?>">
  </label>

  <label class="champ" title="Gardes qui commencent à partir de cette date">
    <span>📅</span>
    <input type="date" name="date"
           value="<?= htmlspecialchars($_GET['date'] ?? '') ?>">
  </label>

  <label class="champ">
    <span>🐾</span>
    <select name="type_animal">
      <option value="">Tous les animaux</option>
      <?php foreach (['Chat', 'Chien', 'Autre'] as $t): ?>
        <option value="<?= $t ?>"
          <?= ($_GET['type_animal'] ?? '') === $t ? 'selected' : '' ?>>
          <?= $t ?>
        </option>
      <?php endforeach; ?>
    </select>
  </label>

  <button type="submit" class="btn btn--corail">🔍 Rechercher</button>
</div>
