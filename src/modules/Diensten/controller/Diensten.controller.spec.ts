
import { Test, TestingModule } from '@nestjs/testing';
import { DienstenController } from './Diensten.controller';
import { DienstenService } from '../service/Diensten.service';

describe('DienstenController', () => {
  let controller: DienstenController;

  beforeEach(async () => {
    const module: TestingModule = await Test.createTestingModule({
      controllers: [DienstenController],
      providers: [{provide: DienstenService, useValue: jest.fn()}],
    }).compile();

    controller = module.get<DienstenController>(DienstenController);
  });

  it('should be defined', () => {
    expect(controller).toBeDefined();
  });
});
