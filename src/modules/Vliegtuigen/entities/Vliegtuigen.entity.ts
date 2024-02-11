import { AfterLoad, Column, Entity, Index, JoinColumn, ManyToOne } from 'typeorm';
import { TypeEntity } from '../../Types/entities/Type.entity';
import { Exclude, Expose } from 'class-transformer';
import { IHeliosDatabaseEntity } from '../../../core/base/IHeliosDatabaseEntity';

@Entity('ref_vliegtuigen')
@Index('VERWIJDERD', ['VERWIJDERD'])
export class VliegtuigenEntity extends IHeliosDatabaseEntity{
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

  @ManyToOne(() => TypeEntity)
  @JoinColumn({ name: "TYPE_ID" })
  @Exclude()
  TYPE: TypeEntity | null;

  @Expose()
  get REG_CALL(): string {
    return `${this.REGISTRATIE} (${this.CALLSIGN ?? ''})`;
  }

  VLIEGTUIGTYPE: string;

  // todo wanneer competenties af zijn
  BEVOEGDHEID_LOKAAL: string;
  BEVOEGDHEID_OVERLAND: string;

  @AfterLoad()
  createComputedFields() {
    this.VLIEGTUIGTYPE = this.TYPE?.OMSCHRIJVING ?? null;

    // todo:
    // Assuming you have the BEVOEGDHEID_LOKAAL and BEVOEGDHEID_OVERLAND entities loaded
    // this.BEVOEGDHEID_LOKAAL = this.BEVOEGDHEID_LOKAAL.OMSCHRIJVING;
    // this.BEVOEGDHEID_OVERLAND = this.BEVOEGDHEID_OVERLAND.OMSCHRIJVING;
  }
}
