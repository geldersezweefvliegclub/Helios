import { Column, Entity } from 'typeorm';
import { IHeliosDatabaseEntity } from '../../../core/base/IHeliosDatabaseEntity';

@Entity('Leden')
export class LedenEntity extends IHeliosDatabaseEntity {
  @Column({ type: 'varchar', length: 255 })
  NAAM: string;

  @Column({ type: 'varchar', length: 15, nullable: true })
  VOORNAAM: string | null;

  @Column({ type: 'varchar', length: 8, nullable: true })
  TUSSENVOEGSEL: string | null;

  @Column({ type: 'varchar', length: 30, nullable: true })
  ACHTERNAAM: string | null;

  @Column({ type: 'varchar', length: 50, nullable: true })
  ADRES: string | null;

  @Column({ type: 'varchar', length: 10, nullable: true })
  POSTCODE: string | null;

  @Column({ type: 'varchar', length: 50, nullable: true })
  WOONPLAATS: string | null;

  @Column({ type: 'varchar', length: 255, nullable: true })
  TELEFOON: string | null;

  @Column({ type: 'varchar', length: 255, nullable: true })
  MOBIEL: string | null;

  @Column({ type: 'varchar', length: 255, nullable: true })
  NOODNUMMER: string | null;

  @Column({ type: 'varchar', length: 45, nullable: true })
  EMAIL: string | null;

  @Column({ type: 'varchar', length: 10, nullable: true })
  LIDNR: string | null;

  @Column({ type: 'mediumint', unsigned: true })
  LIDTYPE_ID: number;

  @Column({ type: 'mediumint', unsigned: true, nullable: true })
  STATUSTYPE_ID: number | null;

  @Column({ type: 'mediumint', unsigned: true, nullable: true })
  ZUSTERCLUB_ID: number | null;

  @Column({ type: 'mediumint', unsigned: true, nullable: true })
  BUDDY_ID: number | null;

  @Column({ type: 'mediumint', unsigned: true, nullable: true })
  BUDDY_ID2: number | null;

  @Column({ type: 'tinyint', unsigned: true, default: 0 })
  LIERIST: number;

  @Column({ type: 'tinyint', unsigned: true, default: 0 })
  LIERIST_IO: number;

  @Column({ type: 'tinyint', unsigned: true, default: 0 })
  STARTLEIDER: number;

  @Column({ type: 'tinyint', unsigned: true, default: 0 })
  INSTRUCTEUR: number;

  @Column({ type: 'tinyint', unsigned: true, default: 0 })
  CIMT: number;

  @Column({ type: 'tinyint', unsigned: true, default: 0 })
  DDWV_CREW: number;

  @Column({ type: 'tinyint', unsigned: true, default: 0 })
  DDWV_BEHEERDER: number;

  @Column({ type: 'tinyint', unsigned: true, default: 0 })
  BEHEERDER: number;

  @Column({ type: 'tinyint', unsigned: true, default: 0 })
  STARTTOREN: number;

  @Column({ type: 'tinyint', unsigned: true, default: 0 })
  ROOSTER: number;

  @Column({ type: 'tinyint', unsigned: true, default: 0 })
  SLEEPVLIEGER: number;

  @Column({ type: 'tinyint', unsigned: true, default: 0 })
  RAPPORTEUR: number;

  @Column({ type: 'tinyint', unsigned: true, default: 0 })
  GASTENVLIEGER: number;

  @Column({ type: 'tinyint', unsigned: true, default: 0 })
  TECHNICUS: number;

  @Column({ type: 'tinyint', unsigned: true, default: 0 })
  CLUBBLAD_POST: number;

  @Column({ type: 'tinyint', unsigned: true, default: 0 })
  ZELFSTART_ABONNEMENT: number;

  @Column({ type: 'date', nullable: true })
  MEDICAL: Date | null;

  @Column({ type: 'date', nullable: true })
  GEBOORTE_DATUM: Date | null;

  @Column({ type: 'varchar', length: 45, nullable: true })
  INLOGNAAM: string | null;

  @Column({ type: 'varchar', length: 255, nullable: true })
  WACHTWOORD: string | null;

  @Column({ type: 'varchar', length: 255, nullable: true })
  SECRET: string | null;

  @Column({ type: 'tinyint', unsigned: true, default: 0 })
  AUTH: number;

  @Column({ type: 'varchar', length: 255, nullable: true })
  AVATAR: string | null;

  @Column({ type: 'tinyint', unsigned: true, default: 0 })
  STARTVERBOD: number;

  @Column({ type: 'tinyint', unsigned: true, default: 0 })
  PRIVACY: number;

  @Column({ type: 'varchar', length: 25, nullable: true })
  SLEUTEL1: string | null;

  @Column({ type: 'varchar', length: 25, nullable: true })
  SLEUTEL2: string | null;

  @Column({ type: 'varchar', length: 25, nullable: true })
  KNVVL_LIDNUMMER: string | null;

  @Column({ type: 'varchar', length: 25, nullable: true })
  BREVET_NUMMER: string | null;

  @Column({ type: 'tinyint', unsigned: true, default: 0 })
  EMAIL_DAGINFO: number;

  @Column({ type: 'text', nullable: true })
  OPMERKINGEN: string | null;

  @Column({ type: 'float', default: 0 })
  TEGOED: number;
}
