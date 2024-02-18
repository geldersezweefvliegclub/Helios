
import { Injectable } from '@nestjs/common';
import { Repository } from 'typeorm';
import { CompetentiesEntity } from '../entities/Competenties.entity';
import { InjectRepository } from '@nestjs/typeorm';
import { IHeliosService } from '../../../core/base/IHelios.service';
import { AuditEntity } from '../../../core/entities/Audit.entity';
import {CompetentiesViewEntity} from "../entities/CompetentiesView.entity";

@Injectable()
export class CompetentiesService extends IHeliosService<CompetentiesEntity, CompetentiesViewEntity> {
  constructor(
    @InjectRepository(CompetentiesEntity) protected readonly repository: Repository<CompetentiesEntity>,
    @InjectRepository(CompetentiesViewEntity) protected readonly viewRepository: Repository<CompetentiesViewEntity>,
    @InjectRepository(AuditEntity) protected readonly auditRepository: Repository<AuditEntity>
  ) {
    super(repository, viewRepository, auditRepository);
  }
}