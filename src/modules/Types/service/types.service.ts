import {Injectable, NotImplementedException} from '@nestjs/common';
import {Repository} from "typeorm";
import {TypeEntity} from "../entities/Type.entity";
import {CreateTableDto} from "../DTO/CreateTableDto";
import {SaveObjectDto} from "../DTO/SaveObjectDto";
import {InjectRepository} from "@nestjs/typeorm";

@Injectable()
export class TypesService {
    constructor(
        @InjectRepository(TypeEntity) private readonly typesRepository: Repository<TypeEntity>
    )
    {
    }

    async createTable(createTypeDto: CreateTableDto) {
        throw new NotImplementedException();
    }

    async createViews() {
        throw new NotImplementedException();
    }

    async getObject(id: number) {
        return this.typesRepository.findOne({where: {ID: id}});
    }

    async deleteObject(id: number) {
        return this.typesRepository.delete(id);
    }

    async restoreObject(id: number) {
        throw new NotImplementedException();
    }

    async saveObject(saveObjectDto: SaveObjectDto) {
        throw new NotImplementedException();
        //return this.typesRepository.save(saveObjectDto);
    }
}
