import { ObjectLiteral } from 'typeorm';

export interface IHeliosEntity extends ObjectLiteral {
  ID: number;
  VERWIJDERD: boolean;
  LAATSTE_AANPASSING: Date;
}
