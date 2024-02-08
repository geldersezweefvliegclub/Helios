import { ObjectLiteral } from 'typeorm';

// todo: only sys_sync table does not have VERWIJDERD
/**
 * Base class for all objects related to Helios, in the database or DTOs.
 */
export interface IHeliosObject extends ObjectLiteral {
  ID: number;
  VERWIJDERD: boolean;
  LAATSTE_AANPASSING: Date;
}
