import {
  Column,
  Entity,
  ManyToOne,
  JoinColumn,
  Index
} from 'typeorm';
import { IHeliosDatabaseEntity } from '../base/IHeliosDatabaseEntity';
import { LedenEntity } from '../../modules/Leden/entities/Leden.entity';

@Entity('audit', { schema: 'gezcor_gezcorgdevelopment5a64' })
@Index('LID_ID', ['LID_ID'])
export class AuditEntity extends IHeliosDatabaseEntity {
  @Column('date', { name: 'DATUM', nullable: false })
  DATUM: string;

  @Column('mediumint', { name: 'LID_ID', nullable: false, unsigned: true })
  LID_ID: number;

  @Column('varchar', { name: 'TABEL', length: 25, nullable: true })
  TABEL: string | null;

  @Column('varchar', { name: 'TABEL_NAAM', length: 25, nullable: true })
  TABEL_NAAM: string | null;

  @Column('varchar', { name: 'ACTIE', length: 15, nullable: true })
  ACTIE: string | null;

  @Column('mediumint', { name: 'OBJECT_ID', nullable: true, unsigned: true })
  OBJECT_ID: number | null;

  @Column('text', { name: 'VOOR', nullable: true })
  VOOR: string | null;

  @Column('text', { name: 'DATA', nullable: true })
  DATA: string | null;

  @Column('text', { name: 'RESULTAAT', nullable: true })
  RESULTAAT: string | null;

  @ManyToOne(() => LedenEntity, {
    onDelete: 'RESTRICT',
    onUpdate: 'RESTRICT',
  })
  @JoinColumn([{ name: 'LID_ID', referencedColumnName: 'ID' }])
  refLeden: LedenEntity;
}
