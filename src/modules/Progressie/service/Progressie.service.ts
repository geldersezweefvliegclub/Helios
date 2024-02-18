import {Injectable} from '@nestjs/common';
import {FindManyOptions, Repository} from 'typeorm';
import {ProgressieEntity} from '../entities/Progressie.entity';
import {InjectRepository} from '@nestjs/typeorm';
import {IHeliosService} from '../../../core/base/IHelios.service';
import {AuditEntity} from '../../../core/entities/Audit.entity';
import {ProgressieKaartFilterDTO} from "../DTO/ProgressieKaartFilterDTO";
import {CompetentiesEntity} from "../../Competenties/entities/Competenties.entity";
import {GetObjectsResponse} from "../../../core/base/GetObjectsResponse";
import {ProgressiekaartDTO} from "../DTO/ProgressiekaartDTO";
import { ProgressieViewEntity } from '../entities/ProgressieView.entity';

@Injectable()
export class ProgressieService extends IHeliosService<ProgressieEntity, ProgressieViewEntity> {
  constructor(
    @InjectRepository(ProgressieEntity) protected readonly repository: Repository<ProgressieEntity>,
    @InjectRepository(ProgressieViewEntity) protected readonly viewRepository: Repository<ProgressieViewEntity>,
    @InjectRepository(CompetentiesEntity) protected readonly competentiesRepository: Repository<CompetentiesEntity>,
    @InjectRepository(AuditEntity) protected readonly auditRepository: Repository<AuditEntity>
  ) {
    super(repository, viewRepository, auditRepository);
  }

    async getProgressiekaart(filter: ProgressieKaartFilterDTO): Promise<GetObjectsResponse<ProgressiekaartDTO>> {
        const datasetRaw = await this.competentiesRepository.createQueryBuilder('c')
            .select("t.OMSCHRIJVING as LEERFASE, c.BLOK, c.ONDERWERP, c.DOCUMENTATIE, p.OPMERKINGEN, LI.NAAM as INSTRUCTEUR_NAAM, p.INGEVOERD, p.SCORE, p.GELDIG_TOT, c.LEERFASE_ID, c.ID, p.ID as PROGRESSIE_ID, c.BLOK_ID")
            .leftJoin(
                (qb) => qb
                    .select('*')
                    .from(ProgressieEntity, 'p')
                    .where('p.LID_ID = :lidId', { lidId: filter.LID_ID }),
                'p',
                'c.ID = p.COMPETENTIE_ID'
            )
            .leftJoin('ref_types', 't', 'c.LEERFASE_ID = t.ID')
            .leftJoin('ref_leden', 'LI', 'p.LID_ID = LI.ID')
            .orderBy({
                'c.LEERFASE_ID': 'ASC',
                'c.VOLGORDE': 'ASC',
                'c.ID': 'ASC',
            })
            .getRawMany();

        // todo fix ugly casting
        return this.bouwDatasetResponse(datasetRaw, filter.findOptionsBuilder.findOptions) as unknown as GetObjectsResponse<ProgressiekaartDTO>;
    }
}