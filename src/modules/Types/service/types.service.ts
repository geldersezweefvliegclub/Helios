import { Injectable } from '@nestjs/common';
import { Repository } from 'typeorm';
import { TypeEntity } from '../entities/Type.entity';
import { InjectRepository } from '@nestjs/typeorm';
import { IHeliosService } from '../../../core/base/IHelios.service';
import { AuditEntity } from '../../../core/entities/Audit.entity';
import {TypeViewEntity} from "../entities/TypeView.entity";

@Injectable()
export class TypesService extends IHeliosService<TypeEntity, TypeViewEntity> {
  constructor(@InjectRepository(TypeEntity) protected readonly repository: Repository<TypeEntity>,
              @InjectRepository(TypeViewEntity) protected readonly viewRepository: Repository<TypeViewEntity>,
              @InjectRepository(AuditEntity) protected readonly auditRepository: Repository<AuditEntity>,
  ) {
    super(repository, viewRepository, auditRepository);
  }
}
