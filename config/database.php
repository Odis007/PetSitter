<?php

// Connexion PDO partagée par tout le site
class Database {
  private static ?PDO $instance = null;

  public static function get(): PDO {
    // On se connecte seulement la première fois, ensuite on réutilise la connexion
    if (self::$instance === null) {
      self::$instance = new PDO(
        "mysql:host=localhost;dbname=petsitter;charset=utf8mb4",
        "root", ""
      );
      // Afficher les erreurs SQL au lieu de les ignorer
      self::$instance->setAttribute(
        PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION
      );
    }
    return self::$instance;
  }
}
