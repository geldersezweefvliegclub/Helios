/**
 * This script is used to create a new "Helios" module in a NestJS application, for a specific entity.
 * It creates the necessary directories and files for the module which inherit from the correct Helios base-classes.
 *
 * It creates a new module file, a controller, a DTO, an entity, and a service in the following file structure:
 * src/modules/ModuleName
 * ├── controller
 * │   ├── ModuleName.controller.ts
 * │   └── ModuleName.controller.spec.ts
 * ├── DTO
 * │   └── ModuleNameGetObjectsFilterDTO.ts
 * ├── entities
 * │   ├── ModuleName.entity.ts
 * │   └── ModuleName.entity.spec.ts
 * └── service
 *    ├── ModuleName.service.ts
 *    └── ModuleName.service.spec.ts
 * ModuleName.module.ts
 *
 * The script takes one argument, which is the name of the module to create.
 *
 * Usage: node create-module.js ModuleName
 */

const fs = require('fs');
const path = require('path');

/**
 * Creates a new module with the given name.
 *
 * @param {string} moduleName - The name of the module to create.
 */
function createModule(moduleName) {
  const modulePath = path.join('src', 'modules', moduleName);
  fs.mkdirSync(modulePath, { recursive: true });

  const directories = ['controller', 'DTO', 'entities', 'service'];
  directories.forEach(dir => {
    fs.mkdirSync(path.join(modulePath, dir));
  });

  const files = {
    [`${moduleName}.module.ts`]: `
import { Module } from '@nestjs/common';
import { ${moduleName}Controller } from './controller/${moduleName}.controller';
import { ${moduleName}Service } from './service/${moduleName}.service';
import { ${moduleName}Entity } from './entities/${moduleName}.entity';
import { TypeOrmModule } from '@nestjs/typeorm';
import { AuditEntity } from '../../core/entities/Audit.entity';

@Module({
    imports: [
        TypeOrmModule.forFeature([${moduleName}Entity, AuditEntity])
    ],
    controllers: [${moduleName}Controller],
    providers: [${moduleName}Service],
})
export class ${moduleName}Module {
}`,

    [`controller/${moduleName}.controller.ts`]: `
import { Controller } from '@nestjs/common';
import { ApiTags } from '@nestjs/swagger';

@Controller('${moduleName}')
@ApiTags('${moduleName}')
export class ${moduleName}Controller {
  constructor() {
  }
}`,
    [`controller/${moduleName}.controller.spec.ts`]: `
import { Test, TestingModule } from '@nestjs/testing';
import { ${moduleName}Controller } from './${moduleName}.controller';

describe('${moduleName}Controller', () => {
  let controller: ${moduleName}Controller;

  beforeEach(async () => {
    const module: TestingModule = await Test.createTestingModule({
      controllers: [${moduleName}Controller],
    }).compile();

    controller = module.get<${moduleName}Controller>(${moduleName}Controller);
  });

  it('should be defined', () => {
    expect(controller).toBeDefined();
  });
});
`,

    [`DTO/${moduleName}GetObjectsFilterDTO.ts`]: `
import { GetObjectsFilterDTO } from '../../../core/base/GetObjectsFilterDTO';
import { ${moduleName}Entity } from '../entities/${moduleName}.entity';

export class ${moduleName}GetObjectsFilterDTO extends GetObjectsFilterDTO<${moduleName}Entity> {
  // Add your properties here
}`,
    [`entities/${moduleName}.entity.ts`]: `
import { IHeliosDatabaseEntity } from '../../../core/base/IHeliosDatabaseEntity';
import { Entity } from 'typeorm';

@Entity('${moduleName}')
export class ${moduleName}Entity extends IHeliosDatabaseEntity{
  // Add your properties here
}`,
    [`entities/${moduleName}.entity.spec.ts`]: `
import { ${moduleName}Entity } from './${moduleName}.entity';

describe('${moduleName}Entity', () => {
  it('should be defined', () => {
    expect(new ${moduleName}Entity()).toBeDefined();
  });
});
`,

    [`service/${moduleName}.service.ts`]: `
import { Injectable } from '@nestjs/common';
import { Repository } from 'typeorm';
import { ${moduleName}Entity } from '../entities/${moduleName}.entity';
import { InjectRepository } from '@nestjs/typeorm';
import { IHeliosService } from '../../../core/base/IHelios.service';
import { AuditEntity } from '../../../core/entities/Audit.entity';

@Injectable()
export class ${moduleName}Service extends IHeliosService<${moduleName}Entity> {
  constructor(
    @InjectRepository(${moduleName}Entity) protected readonly repository: Repository<${moduleName}Entity>,
    @InjectRepository(AuditEntity) protected readonly auditRepository: Repository<AuditEntity>
  ) {
    super(repository, auditRepository);
  }
}`,
    [`service/${moduleName}.service.spec.ts`]: `
import { Test, TestingModule } from '@nestjs/testing';
import { ${moduleName}Service } from './${moduleName}.service';
import { getRepositoryToken } from '@nestjs/typeorm';
import { ${moduleName}Entity } from '../entities/${moduleName}.entity';
import { Repository } from 'typeorm';
import { AuditEntity } from '../../../core/entities/Audit.entity';

describe('${moduleName}Service', () => {
  let service: ${moduleName}Service;
  // eslint-disable-next-line @typescript-eslint/no-unused-vars
  let mockRepository: Repository<${moduleName}Entity>;

  beforeEach(async () => {
    const module: TestingModule = await Test.createTestingModule({
      providers: [
        ${moduleName}Service,
        { provide: getRepositoryToken(${moduleName}Entity), useValue: jest.fn()},
        { provide: getRepositoryToken(AuditEntity), useClass: jest.fn() },
      ],
    }).compile();

    service = module.get<${moduleName}Service>(${moduleName}Service);
    mockRepository = module.get<Repository<${moduleName}Entity>>(getRepositoryToken(${moduleName}Entity));
  });

  it('should be defined', () => {
    expect(service).toBeDefined();
  });
});`,
  };

  for (const [fileName, content] of Object.entries(files)) {
    console.log(`Creating file: ${path.join(modulePath, fileName)}`);
    try {
    fs.writeFileSync(path.join(modulePath, fileName), content);

    } catch (e){
      console.log(`Warning: failed to create file ${path.join(modulePath, fileName)}. Does it already exist?`)
    }
  }
}

const moduleName = process.argv[2];

if (!moduleName) {
  console.log('Please provide a module name');
  process.exit(1);
}

createModule(moduleName);
console.log()
console.log(`Module ${moduleName} created successfully.`);
console.log("Don't forget to add the new module to the app.module.ts file.");
console.log("Also check your Entity to make sure it defines the correct table name inside the @Entity decorator.");
