import { IHeliosDatabaseEntity } from '../../../core/base/IHeliosDatabaseEntity';
import { Column, Entity, Index, JoinColumn, ManyToOne } from 'typeorm';
import { LedenEntity } from '../../Leden/entities/Leden.entity';
import { TypeEntity } from '../../Types/entities/Type.entity';
import { RoosterEntity } from '../../Rooster/entities/Rooster.entity';

@Entity('oper_diensten')
@Index('LID_ID', ['LID_ID'])
@Index('ROOSTER_ID', ['ROOSTER_ID'])
@Index('DATUM', ['DATUM'])
@Index('TYPE_DIENST_ID', ['TYPE_DIENST_ID'])
@Index('VERWIJDERD', ['VERWIJDERD'])
export class DienstenEntity extends IHeliosDatabaseEntity{
  @Column({type: 'date', nullable: false})
  public DATUM: Date;

  @Column({type: 'mediumint', nullable: false})
  public ROOSTER_ID: number;

  @Column({type: 'mediumint', nullable: false})
  public LID_ID: number;

  @Column({type: 'mediumint', nullable: true})
  public TYPE_DIENST_ID: number;

  @Column({type: 'mediumint', nullable: true})
  public INGEVOERD_DOOR_ID: number;

  @Column({type: 'boolean', nullable: true})
  public AANWEZIG: boolean;

  @Column({type: 'boolean', nullable: true})
  public AFWEZIG: boolean;

  @Column({type: 'boolean', nullable: false})
  public UITBETAALD: boolean;

  @ManyToOne(() => LedenEntity)
  @JoinColumn({name: 'INGEVOERD_DOOR_ID'})
  public INGEVOERD_DOOR: LedenEntity;

  @ManyToOne(() => LedenEntity)
  @JoinColumn({name: 'LID_ID'})
  public LID: LedenEntity;

  @ManyToOne(() => TypeEntity)
  @JoinColumn({name: 'TYPE_DIENST_ID'})
  public DIENST_TYPE: TypeEntity;

  @ManyToOne(() => RoosterEntity)
  @JoinColumn({name: 'ROOSTER_ID'})
  public ROOSTER: RoosterEntity;
}
