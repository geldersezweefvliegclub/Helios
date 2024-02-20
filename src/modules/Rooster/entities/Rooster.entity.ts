
import { IHeliosDatabaseEntity } from '../../../core/base/IHeliosDatabaseEntity';
import { Column, Entity } from 'typeorm';

@Entity('oper_rooster')
export class RoosterEntity extends IHeliosDatabaseEntity{
  @Column({ type: 'date', nullable: false })
  public DATUM: Date;

  @Column({ type: 'boolean', nullable: false, default: false })
  public DDWV: boolean;

  @Column({ type: 'boolean', nullable: false, default: true })
  public CLUB_BEDRIJF: boolean;

  @Column({ type: 'int', nullable: false, default: 3 })
  public MIN_SLEEPSTART: number;

  @Column({ type: 'int', nullable: false, default: 10 })
  public MIN_LIERSTART: number;

  @Column({ type: 'text', nullable: true })
  public OPMERKINGEN: string;
}
