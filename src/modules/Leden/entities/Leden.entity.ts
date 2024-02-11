import { AfterLoad, Column, Entity, Index, JoinColumn, ManyToOne } from 'typeorm';
import { IHeliosDatabaseEntity } from '../../../core/base/IHeliosDatabaseEntity';
import { Exclude, Expose, Transform } from 'class-transformer';
import { TypeEntity } from '../../Types/entities/Type.entity';

@Entity('ref_leden')
@Index('NAAM', ['NAAM'])
@Index('LIDTYPE_ID', ['LIDTYPE_ID'])
@Index('STARTLEIDER', ['STARTLEIDER'])
@Index('INSTRUCTEUR', ['INSTRUCTEUR'])
@Index('LIERIST', ['LIERIST'])
@Index('VERWIJDERD', ['VERWIJDERD'])
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

  @Column({ type: 'boolean', unsigned: true, default: 0 })
  LIERIST: boolean;

  // todo typo in column name? For now to keep the contract the same, transform to _IO with class-transformer but internally use _LIO
  @Column({ type: 'boolean', unsigned: true, default: 0, name: 'LIERIST_IO' })
  @Expose({ name: 'LIERIST_IO' })
  LIERIST_LIO: boolean;

  @Column({ type: 'boolean', unsigned: true, default: 0 })
  STARTLEIDER: boolean;

  @Column({ type: 'boolean', unsigned: true, default: 0 })
  INSTRUCTEUR: boolean;

  @Column({ type: 'boolean', unsigned: true, default: 0 })
  CIMT: boolean;

  @Column({ type: 'boolean', unsigned: true, default: 0 })
  DDWV_CREW: boolean;

  @Column({ type: 'boolean', unsigned: true, default: 0 })
  DDWV_BEHEERDER: boolean;

  @Column({ type: 'boolean', unsigned: true, default: 0 })
  BEHEERDER: boolean;

  @Column({ type: 'boolean', unsigned: true, default: 0 })
  STARTTOREN: boolean;

  @Column({ type: 'boolean', unsigned: true, default: 0 })
  ROOSTER: boolean;

  @Column({ type: 'boolean', unsigned: true, default: 0 })
  SLEEPVLIEGER: boolean;

  @Column({ type: 'boolean', unsigned: true, default: 0 })
  RAPPORTEUR: boolean;

  @Column({ type: 'boolean', unsigned: true, default: 0 })
  GASTENVLIEGER: boolean;

  @Column({ type: 'boolean', unsigned: true, default: 0 })
  TECHNICUS: boolean;

  @Column({ type: 'boolean', unsigned: true, default: 0 })
  CLUBBLAD_POST: boolean;

  @Column({ type: 'boolean', unsigned: true, default: 0 })
  ZELFSTART_ABONNEMENT: boolean;

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

  @Column({ type: 'boolean', unsigned: true, default: 0 })
  AUTH: boolean;

  @Column({ type: 'varchar', length: 255, nullable: true })
  AVATAR: string | null;

  @Column({ type: 'boolean', unsigned: true, default: 0 })
  STARTVERBOD: boolean;

  @Column({ type: 'boolean', unsigned: true, default: 0 })
  PRIVACY: boolean;

  @Column({ type: 'varchar', length: 25, nullable: true })
  SLEUTEL1: string | null;

  @Column({ type: 'varchar', length: 25, nullable: true })
  SLEUTEL2: string | null;

  @Column({ type: 'varchar', length: 25, nullable: true })
  KNVVL_LIDNUMMER: string | null;

  @Column({ type: 'varchar', length: 25, nullable: true })
  BREVET_NUMMER: string | null;

  @Column({ type: 'boolean', unsigned: true, default: 0 })
  EMAIL_DAGINFO: boolean;

  @Column({ type: 'text', nullable: true })
  OPMERKINGEN: string | null;

  @Column({ type: 'float', default: 0 })
  TEGOED: number;

  @ManyToOne(() => TypeEntity, (lidtype) => lidtype.ID, { eager: true })
  @JoinColumn({ name: 'LIDTYPE_ID' })
  @Transform(({ value }) => value?.OMSCHRIJVING ?? null, { toPlainOnly: true })
  LIDTYPE: string | null;

  @ManyToOne(() => LedenEntity, leden => leden.ID)
  @JoinColumn({ name: 'ZUSTERCLUB_ID' })
  @Transform(({ value }) => value?.NAAM ?? null, { toPlainOnly: true })
  ZUSTERCLUB: LedenEntity | null;

  @ManyToOne(() => LedenEntity, leden => leden.ID)
  @JoinColumn({ name: 'BUDDY_ID' })
  @Transform(({ value }) => value?.NAAM ?? null, { toPlainOnly: true })
  BUDDY: LedenEntity | null;

  @ManyToOne(() => LedenEntity, leden => leden.ID)
  @JoinColumn({ name: 'BUDDY_ID2' })
  @Transform(({ value }) => value?.NAAM ?? null, { toPlainOnly: true })
  BUDDY2: LedenEntity | null;

  // TODO: PAX field als competenties gebouwd zijn
}
