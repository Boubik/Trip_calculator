-- MySQL Script generated by MySQL Workbench
-- Tue Jan 28 08:40:29 2025
-- Model: New Model    Version: 1.0
-- MySQL Workbench Forward Engineering

SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';

-- -----------------------------------------------------
-- Schema trip_calculator
-- -----------------------------------------------------

-- -----------------------------------------------------
-- Schema trip_calculator
-- -----------------------------------------------------
CREATE SCHEMA IF NOT EXISTS `trip_calculator` DEFAULT CHARACTER SET utf8 ;
USE `trip_calculator` ;

-- -----------------------------------------------------
-- Table `trip_calculator`.`category`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `trip_calculator`.`category` (
  `name` VARCHAR(100) NOT NULL,
  PRIMARY KEY (`name`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `trip_calculator`.`user`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `trip_calculator`.`user` (
  `name` VARCHAR(100) NOT NULL,
  `password` VARCHAR(512) NOT NULL,
  PRIMARY KEY (`name`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `trip_calculator`.`item_set`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `trip_calculator`.`item_set` (
  `id` BIGINT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `owner` VARCHAR(100) NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_item_set_user1_idx` (`owner` ASC) VISIBLE,
  CONSTRAINT `fk_item_set_user1`
    FOREIGN KEY (`owner`)
    REFERENCES `trip_calculator`.`user` (`name`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `trip_calculator`.`currency`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `trip_calculator`.`currency` (
  `name` VARCHAR(20) NOT NULL,
  PRIMARY KEY (`name`),
  UNIQUE INDEX `currency_UNIQUE` (`name` ASC) VISIBLE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `trip_calculator`.`item`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `trip_calculator`.`item` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `price` DECIMAL(20,2) UNSIGNED NOT NULL,
  `payer` VARCHAR(100) NOT NULL,
  `note` TEXT(255) NULL,
  `category_name` VARCHAR(100) NOT NULL,
  `item_set_id` BIGINT NOT NULL,
  `currency_name` VARCHAR(20) NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_item_category_idx` (`category_name` ASC) VISIBLE,
  INDEX `fk_item_item_set1_idx` (`item_set_id` ASC) VISIBLE,
  INDEX `fk_item_currency1_idx` (`currency_name` ASC) VISIBLE,
  CONSTRAINT `fk_item_category`
    FOREIGN KEY (`category_name`)
    REFERENCES `trip_calculator`.`category` (`name`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_item_item_set1`
    FOREIGN KEY (`item_set_id`)
    REFERENCES `trip_calculator`.`item_set` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_item_currency1`
    FOREIGN KEY (`currency_name`)
    REFERENCES `trip_calculator`.`currency` (`name`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `trip_calculator`.`user_has_item_set`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `trip_calculator`.`user_has_item_set` (
  `item_set_id` BIGINT NOT NULL,
  `user_name` VARCHAR(100) NOT NULL,
  PRIMARY KEY (`item_set_id`, `user_name`),
  INDEX `fk_item_set_has_user_user1_idx` (`user_name` ASC) VISIBLE,
  INDEX `fk_item_set_has_user_item_set1_idx` (`item_set_id` ASC) VISIBLE,
  CONSTRAINT `fk_item_set_has_user_item_set1`
    FOREIGN KEY (`item_set_id`)
    REFERENCES `trip_calculator`.`item_set` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_item_set_has_user_user1`
    FOREIGN KEY (`user_name`)
    REFERENCES `trip_calculator`.`user` (`name`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `trip_calculator`.`item_has_user`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `trip_calculator`.`item_has_user` (
  `item_id` BIGINT UNSIGNED NOT NULL,
  `user_name` VARCHAR(100) NOT NULL,
  PRIMARY KEY (`item_id`, `user_name`),
  INDEX `fk_item_has_user_user1_idx` (`user_name` ASC) VISIBLE,
  INDEX `fk_item_has_user_item1_idx` (`item_id` ASC) VISIBLE,
  CONSTRAINT `fk_item_has_user_item1`
    FOREIGN KEY (`item_id`)
    REFERENCES `trip_calculator`.`item` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_item_has_user_user1`
    FOREIGN KEY (`user_name`)
    REFERENCES `trip_calculator`.`user` (`name`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `trip_calculator`.`editors`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `trip_calculator`.`editors` (
  `item_set_id` BIGINT NOT NULL,
  `user_name` VARCHAR(100) NOT NULL,
  PRIMARY KEY (`item_set_id`, `user_name`),
  INDEX `fk_item_set_has_user_user2_idx` (`user_name` ASC) VISIBLE,
  INDEX `fk_item_set_has_user_item_set2_idx` (`item_set_id` ASC) VISIBLE,
  CONSTRAINT `fk_item_set_has_user_item_set2`
    FOREIGN KEY (`item_set_id`)
    REFERENCES `trip_calculator`.`item_set` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_item_set_has_user_user2`
    FOREIGN KEY (`user_name`)
    REFERENCES `trip_calculator`.`user` (`name`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
