
import { Injectable } from '@nestjs/common';
import { Repository } from 'typeorm';
import { LedenEntity } from '../entities/Leden.entity';
import { InjectRepository } from '@nestjs/typeorm';
import { IHeliosService } from '../../../core/base/IHelios.service';
import { AuditEntity } from '../../../core/entities/Audit.entity';

@Injectable()
export class LedenService extends IHeliosService<LedenEntity> {
  constructor(@InjectRepository(LedenEntity) protected readonly repository: Repository<LedenEntity>,
              @InjectRepository(AuditEntity) protected readonly auditRepository: Repository<AuditEntity>,
  ) {
    super(repository, auditRepository);
  }
}
