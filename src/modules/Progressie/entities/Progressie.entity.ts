import {IHeliosDatabaseEntity} from '../../../core/base/IHeliosDatabaseEntity';
import {Column, Entity, JoinColumn, ManyToOne} from 'typeorm';
import {LedenEntity} from '../../Leden/entities/Leden.entity';
import {CompetentiesEntity} from '../../Competenties/entities/Competenties.entity';
import {Exclude} from "class-transformer";

@Entity('oper_progressie')
export class ProgressieEntity extends IHeliosDatabaseEntity {
  @Column()
  LID_ID: number;

  @Column()
  COMPETENTIE_ID: number;

  @Column()
  INSTRUCTEUR_ID: number;

  @Column({ type: 'text', nullable: true })
  OPMERKINGEN: string;

  @Column({ nullable: true })
  LINK_ID: number;

  @Column({ type: 'date', nullable: true })
  GELDIG_TOT: Date;

  @Column({ type: 'smallint', nullable: true })
  SCORE: number;

  @Column({ type: 'date', nullable: true })
  INGEVOERD: Date;

  @ManyToOne(() => LedenEntity)
  @JoinColumn({ name: 'LID_ID' })
  @Exclude()
  LidEntity: LedenEntity | null;

  @ManyToOne(() => CompetentiesEntity)
  @JoinColumn({ name: 'COMPETENTIE_ID' })
  @Exclude()
  CompetentieEntity: CompetentiesEntity | null;

  @ManyToOne(() => LedenEntity)
  @JoinColumn({ name: 'INSTRUCTEUR_ID' })
  @Exclude()
  InstructeurEntity: LedenEntity | null;
}
