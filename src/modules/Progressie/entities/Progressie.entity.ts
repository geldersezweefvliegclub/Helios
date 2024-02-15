import { IHeliosDatabaseEntity } from '../../../core/base/IHeliosDatabaseEntity';
import { Column, Entity, ManyToOne } from 'typeorm';
import { LedenEntity } from '../../Leden/entities/Leden.entity';
import { CompetentiesEntity } from '../../Competenties/entities/Competenties.entity';

@Entity('ref_progressie')
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

  // many to one to LedenEntity based on LID_ID
  @ManyToOne('LedenEntity', 'LID_ID')
  Lid: LedenEntity | null;

  @ManyToOne('CompetentiesEntity', 'COMPETENTIE_ID')
  Competentie: CompetentiesEntity | null;

  @ManyToOne('LedenEntity', 'INSTRUCTEUR_ID')
  Instructeur: LedenEntity | null;
}
