
import { Injectable } from '@nestjs/common';
import { Repository } from 'typeorm';
import { LedenEntity } from '../entities/Leden.entity';
import { InjectRepository } from '@nestjs/typeorm';
import { IHeliosService } from '../../../core/base/IHelios.service';
import { AuditEntity } from '../../../core/entities/Audit.entity';
import {LedenViewEntity} from "../entities/LedenView.entity";

@Injectable()
export class LedenService extends IHeliosService<LedenEntity, LedenViewEntity> {
  constructor(@InjectRepository(LedenEntity) protected readonly repository: Repository<LedenEntity>,
              @InjectRepository(LedenViewEntity) protected readonly viewRepository: Repository<LedenViewEntity>,
              @InjectRepository(AuditEntity) protected readonly auditRepository: Repository<AuditEntity>,
  ) {
    super(repository, viewRepository, auditRepository);
  }
}
