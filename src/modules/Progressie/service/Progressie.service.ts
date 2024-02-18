
import { Injectable } from '@nestjs/common';
import { Repository } from 'typeorm';
import { ProgressieEntity } from '../entities/Progressie.entity';
import { InjectRepository } from '@nestjs/typeorm';
import { IHeliosService } from '../../../core/base/IHelios.service';
import { AuditEntity } from '../../../core/entities/Audit.entity';
import {ProgressieKaartFilterDTO} from "../DTO/ProgressieKaartFilterDTO";

@Injectable()
export class ProgressieService extends IHeliosService<ProgressieEntity> {
  constructor(
    @InjectRepository(ProgressieEntity) protected readonly repository: Repository<ProgressieEntity>,
    @InjectRepository(AuditEntity) protected readonly auditRepository: Repository<AuditEntity>
  ) {
    super(repository, auditRepository);
  }
}