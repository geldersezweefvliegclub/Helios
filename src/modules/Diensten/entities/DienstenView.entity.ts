
import { IHeliosDatabaseEntity } from '../../../core/base/IHeliosDatabaseEntity';
import { ViewEntity } from 'typeorm';

@ViewEntity({name: 'TODO!', expression: "SELECT * FROM TODO!"})
export class DienstenViewEntity extends IHeliosDatabaseEntity{
  // Add your properties here with @ViewColumn
}