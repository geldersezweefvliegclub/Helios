import { Column, Entity, Index, JoinColumn, ManyToOne } from 'typeorm';
import { IHeliosDatabaseEntity } from '../../../core/base/IHeliosDatabaseEntity';
import { TypeGroepEntity } from '../../TypesGroepen/entities/TypeGroep.entity';

@Entity('ref_types')
@Index('GROEP', ['GROEP'])
@Index('VERWIJDERD', ['VERWIJDERD'])
export class TypeEntity extends IHeliosDatabaseEntity{
    @Column({ type: 'smallint', unsigned: true })
    GROEP: number;

    @Column({ type: 'varchar', length: 10, nullable: true })
    CODE: string | null;

    @Column({ type: 'varchar', length: 25, nullable: true })
    EXT_REF: string | null;

    @Column({ type: 'varchar', length: 75 })
    OMSCHRIJVING: string;

    @Column({ type: 'tinyint', unsigned: true, nullable: true })
    SORTEER_VOLGORDE: number | null;

    @Column("boolean", { default: false, name: "READ_ONLY" })
    READ_ONLY: boolean;

    @Column({ type: 'decimal', precision: 6, scale: 2, nullable: true })
    BEDRAG: number | null;

    @Column({ type: 'decimal', precision: 6, scale: 2, nullable: true })
    EENHEDEN: number | null;

    @ManyToOne(() => TypeGroepEntity)
    @JoinColumn({ name: 'GROEP' })
    TYPEGROEP: TypeEntity;
}
