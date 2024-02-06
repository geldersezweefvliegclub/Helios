import { Injectable } from '@nestjs/common';
import { Repository } from 'typeorm';
import { TypeEntity } from '../entities/Type.entity';
import { InjectRepository } from '@nestjs/typeorm';
import { TypesGetObjectsFilterDTO } from '../DTO/TypesGetObjectsFilterDTO';
import { IHeliosService } from '../../../core/base/IHelios.service';

@Injectable()
export class TypesService extends IHeliosService<TypeEntity, TypesGetObjectsFilterDTO> {
  constructor(@InjectRepository(TypeEntity) protected readonly repository: Repository<TypeEntity>) {
    super(repository);
  }
}
