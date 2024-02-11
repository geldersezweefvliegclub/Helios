import { IHeliosObject } from './IHeliosObject';
import { Column, PrimaryGeneratedColumn, UpdateDateColumn } from 'typeorm';

/**
 * Base class for all entities in the Helios database.
 */
export abstract class IHeliosDatabaseEntity implements IHeliosObject {
  @PrimaryGeneratedColumn({ type: 'mediumint', unsigned: true })
  ID: number;

  @Column('boolean', { default: false, name: 'VERWIJDERD' })
  VERWIJDERD: boolean;

  @UpdateDateColumn({ type: 'timestamp', default: () => 'CURRENT_TIMESTAMP', onUpdate: 'CURRENT_TIMESTAMP' })
  LAATSTE_AANPASSING: Date;
}
