import { ObjectLiteral } from 'typeorm';

// todo: only sys_sync table does not have VERWIJDERD
export interface IHeliosEntity extends ObjectLiteral {
  ID: number;
  VERWIJDERD: boolean;
  LAATSTE_AANPASSING: Date;
}
