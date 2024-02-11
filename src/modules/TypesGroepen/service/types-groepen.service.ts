import { Injectable } from '@nestjs/common';
import { Repository } from 'typeorm';
import { TypeGroepEntity } from '../entities/TypeGroep.entity';
import { InjectRepository } from '@nestjs/typeorm';
import { IHeliosService } from '../../../core/base/IHelios.service';
import { AuditEntity } from '../../../core/entities/Audit.entity';

@Injectable()
export class TypesGroepenService extends IHeliosService<TypeGroepEntity> {
  constructor(@InjectRepository(TypeGroepEntity) protected readonly repository: Repository<TypeGroepEntity>,
              @InjectRepository(AuditEntity) protected readonly auditRepository: Repository<AuditEntity>,
  ) {
    super(repository, auditRepository);
  }
}
