import { Entity, PrimaryGeneratedColumn, Column, UpdateDateColumn, Index } from 'typeorm';

@Entity('ref_vliegtuigen')
@Index('VERWIJDERD', ['VERWIJDERD'])
export class VliegtuigenEntity {
  @PrimaryGeneratedColumn({ type: 'mediumint', unsigned: true })
  ID: number;

  @Column({ type: 'varchar', length: 50 })
  REGISTRATIE: string;

  @Column({ type: 'varchar', length: 50, nullable: true })
  CALLSIGN: string | null;

  @Column({ type: 'smallint', unsigned: true })
  ZITPLAATSEN: number;

  @Column("boolean", { default: false })
  CLUBKIST: boolean;

  @Column({ type: 'varchar', length: 50, nullable: true })
  FLARMCODE: string | null;

  @Column("boolean", { default: false })
  ZELFSTART: boolean;

  @Column("boolean", { default: false })
  SLEEPKIST: boolean;


  @Column("boolean", { default: false })
  TMG: boolean;

  @Column({ type: 'smallint', unsigned: true })
  TYPE_ID: number;

  @Column({ type: 'smallint', unsigned: true })
  VOLGORDE: number;

  @Column("boolean", { default: false })
  INZETBAAR: boolean;

  @Column("boolean", { default: false })
  TRAINER: boolean;

  @Column({ type: 'smallint', unsigned: true })
  BEVOEGDHEID_LOKAAL_ID: number;

  @Column({ type: 'smallint', unsigned: true })
  BEVOEGDHEID_OVERLAND_ID: number;



  @Column({ type: 'varchar', length: 255, nullable: true })
  URL: string | null;

  @Column({ type: 'text', nullable: true })
  OPMERKINGEN: string | null;

  @Column("boolean", { default: false })
  VERWIJDERD: boolean;

  @UpdateDateColumn({ type: 'timestamp', default: () => 'CURRENT_TIMESTAMP', onUpdate: 'CURRENT_TIMESTAMP' })
  LAATSTE_AANPASSING: Date;
}
