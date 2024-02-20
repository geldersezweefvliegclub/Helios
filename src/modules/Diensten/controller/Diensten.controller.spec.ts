
import { Test, TestingModule } from '@nestjs/testing';
import { DienstenController } from './Diensten.controller';

describe('DienstenController', () => {
  let controller: DienstenController;

  beforeEach(async () => {
    const module: TestingModule = await Test.createTestingModule({
      controllers: [DienstenController],
    }).compile();

    controller = module.get<DienstenController>(DienstenController);
  });

  it('should be defined', () => {
    expect(controller).toBeDefined();
  });
});
