
import { Test, TestingModule } from '@nestjs/testing';
import { ProgressieController } from './Progressie.controller';

describe('ProgressieController', () => {
  let controller: ProgressieController;

  beforeEach(async () => {
    const module: TestingModule = await Test.createTestingModule({
      controllers: [ProgressieController],
    }).compile();

    controller = module.get<ProgressieController>(ProgressieController);
  });

  it('should be defined', () => {
    expect(controller).toBeDefined();
  });
});
