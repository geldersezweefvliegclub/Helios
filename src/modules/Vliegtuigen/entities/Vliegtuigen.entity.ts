import { Column, Entity, Index, JoinColumn, ManyToOne } from 'typeorm';
import { TypeEntity } from '../../Types/entities/Type.entity';
import { Expose, Transform } from 'class-transformer';
import { IHeliosDatabaseEntity } from '../../../core/base/IHeliosDatabaseEntity';
import { CompetentiesEntity } from '../../Competenties/entities/Competenties.entity';

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
  @Transform(({ value }) => value?.OMSCHRIJVING ?? null)
  VLIEGTUIGTYPE: TypeEntity | null;

  @ManyToOne(() => CompetentiesEntity)
  @JoinColumn({ name: "BEVOEGDHEID_LOKAAL_ID" })
  @Transform(({ value }) => value?.ONDERWERP ?? null)
  BEVOEGDHEID_LOKAAL: CompetentiesEntity | null;

  @ManyToOne(() => CompetentiesEntity)
  @JoinColumn({ name: "BEVOEGDHEID_OVERLAND_ID" })
  @Transform(({ value }) => value?.ONDERWERP ?? null)
  BEVOEGDHEID_OVERLAND: CompetentiesEntity | null;

  @Expose()
  get REG_CALL(): string {
    return `${this.REGISTRATIE} (${this.CALLSIGN ?? ''})`;
  }

}
