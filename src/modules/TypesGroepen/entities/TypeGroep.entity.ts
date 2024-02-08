import { Column, Entity } from 'typeorm';
import { IHeliosDatabaseEntity } from '../../../core/base/IHeliosDatabaseEntity';

@Entity('ref_types_groepen')
export class TypeGroepEntity extends IHeliosDatabaseEntity{
    @Column({ type: 'varchar', length: 10, nullable: true })
    CODE: string;

    @Column({ type: 'varchar', length: 25, nullable: true })
    EXT_REF: string;

    @Column({ type: 'varchar', length: 75 })
    OMSCHRIJVING: string;

    @Column({ type: 'tinyint', nullable: true })
    SORTEER_VOLGORDE: number;

    @Column({ type: 'tinyint', default: 0 })
    READ_ONLY: number;

    @Column({ type: 'tinyint', default: 0 })
    BEDRAG_EENHEDEN: number;
}
