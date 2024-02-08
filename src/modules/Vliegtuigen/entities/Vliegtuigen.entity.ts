import {
  Entity,
  PrimaryGeneratedColumn,
  Column,
  UpdateDateColumn,
  Index,
  ManyToOne,
  JoinColumn,
  AfterLoad,
} from 'typeorm';
import { TypeEntity } from '../../Types/entities/Type.entity';
import { Exclude } from 'class-transformer';

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

  @ManyToOne(() => TypeEntity, {eager: true})
  @JoinColumn({ name: "TYPE_ID" })
  @Exclude()
  Type: TypeEntity | null;

  REG_CALL: string;
  VLIEGTUIGTYPE: string;
  // todo
  BEVOEGDHEID_LOKAAL: string;
  BEVOEGDHEID_OVERLAND: string;

  @AfterLoad()
  createComputedFields() {
    this.TYPE_ID = this.Type?.ID ?? null;
    this.REG_CALL = `${this.REGISTRATIE} (${this.CALLSIGN ?? ''})`;
    this.VLIEGTUIGTYPE = this.Type?.OMSCHRIJVING ?? null;
    // todo:
    // Assuming you have the BEVOEGDHEID_LOKAAL and BEVOEGDHEID_OVERLAND entities loaded
    // this.BEVOEGDHEID_LOKAAL = this.BEVOEGDHEID_LOKAAL.OMSCHRIJVING;
    // this.BEVOEGDHEID_OVERLAND = this.BEVOEGDHEID_OVERLAND.OMSCHRIJVING;
  }
}
