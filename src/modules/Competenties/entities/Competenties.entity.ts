import { IHeliosDatabaseEntity } from '../../../core/base/IHeliosDatabaseEntity';
import { AfterLoad, Column, Entity, Index, JoinColumn, ManyToOne } from 'typeorm';
import { TypeEntity } from '../../Types/entities/Type.entity';
import { Exclude } from 'class-transformer';

@Entity('ref_competenties')
@Index('LEERFASE_ID', ['LEERFASE_ID'])
@Index('VERWIJDERD', ['VERWIJDERD'])
export class CompetentiesEntity extends IHeliosDatabaseEntity{
  @Column({ type: 'smallint', unsigned: true, nullable: true })
  VOLGORDE: number | null;

  @Column({ type: 'mediumint', unsigned: true })
  LEERFASE_ID: number;

  @Column({ type: 'mediumint', unsigned: true, nullable: true })
  BLOK_ID: number | null;

  @Column({ type: 'varchar', length: 7, nullable: true })
  BLOK: string | null;

  @Column({ type: 'varchar', length: 75 })
  ONDERWERP: string;

  @Column({ type: 'varchar', length: 75, nullable: true })
  DOCUMENTATIE: string | null;

  @Column({ type: 'boolean', unsigned: true, default: 0 })
  GELDIGHEID: boolean;

  @Column({ type: 'boolean', unsigned: true, default: 0 })
  SCORE: boolean;

  @ManyToOne(() => TypeEntity, {eager: true})
  @JoinColumn({ name: 'LEERFASE_ID' })
  @Exclude()
  LeerfaseEntity: TypeEntity;

  @ManyToOne(() => CompetentiesEntity)
  @JoinColumn({ name: 'BLOK_ID' })
  @Exclude()
  BlokEntity: CompetentiesEntity;

  LEERFASE: string | null;

  @AfterLoad()
  setComputed() {
    this.LEERFASE = this.LeerfaseEntity?.OMSCHRIJVING ?? null;
  }
}
