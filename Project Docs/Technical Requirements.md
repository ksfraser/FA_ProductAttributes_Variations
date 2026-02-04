# Technical Requirements - FA_ProductAttributes_Variations Plugin

## Platform and Environment

- **FrontAccounting Version**: 2.3.22
- **PHP Version**: 7.3
- **Database**: MySQL (as used by FrontAccounting)
- **Dependencies**: FA_ProductAttributes core module (required)

## Plugin Architecture

### Plugin Responsibilities
The variations plugin extends the core FA_ProductAttributes module:

- **Variation Services**: VariationService, FrontAccountingVariationService, RetroactiveApplicationService
- **UI Extensions**: Extends core attributes tab with variation management UI
- **Business Logic**: Parent-child relationship management, variation generation
- **Database Extensions**: Uses core tables, adds variation-specific relationships

### Extension Points Used
- **attributes_tab_content**: Adds variation UI to core attributes tab
- **attributes_save**: Handles variation-specific save operations
- **product_type_management**: Extends product type functionality

### Service Layer Architecture
- **VariationService**: Core variation business logic (combinations, relationships)
- **FrontAccountingVariationService**: FA-specific operations (product creation, pricing)
- **RetroactiveApplicationService**: Pattern analysis for existing products
- **Hook Integration**: Services integrate with core through extension system

### Database Integration
- **Core Tables Used**: product_attribute_*, stock_master relationships
- **Extension Pattern**: Adds variation-specific data without modifying core schema
- **Relationship Tracking**: Maintains parent-child product hierarchies

## Development Principles

### SOLID Principles
- **Single Responsibility Principle (SRP)**: Each class has one reason to change
- **Open/Closed Principle**: Classes open for extension, closed for modification
- **Liskov Substitution Principle**: Subtypes are substitutable for their base types
- **Interface Segregation Principle**: Clients not forced to depend on methods they don't use
- **Dependency Inversion Principle**: Depend on abstractions, not concretions

### Design Patterns and Practices
- **Dependency Injection (DI)**: Use constructor injection for dependencies
- **Avoid If/Switch Statements**: Use SRP classes and polymorphism instead of conditional logic where possible (following Martin Fowler's Replace Conditional with Polymorphism)
- **DRY (Don't Repeat Yourself)**: Use parent classes, traits, and composition
- **Composition over Inheritance**: Prefer composition where appropriate

## Code Quality

### Documentation
- **PHPDoc**: Comprehensive PHPDoc blocks for all classes, methods, and properties
- **Inline Comments**: Clear comments for complex logic
- **README**: Detailed usage instructions and API documentation

### HTML/UI Framework
- **ksfraser/HTML Library**: Used for all HTML generation instead of FA's built-in functions
- **Direct Instantiation Pattern**: HTML elements created with `new HtmlElement()` instead of builder chains
- **Avoid "Headers Already Sent" Issues**: No immediate echo output; all HTML generated as strings and output at once
- **Reusable Components**: Table classes for displaying data with edit/delete actions, Button classes for OK/Cancel operations
- **Composite Pattern**: Page objects containing tables, forms, fields, and buttons with recursive display() calls
- **SRP UI Components**: Complex UI sections extracted into dedicated classes (e.g., CsvImportForm, SearchUpdateForm) implementing HTML library interfaces
- **Consistent UI**: All forms, tables, and UI elements generated through the library
- **Separation of Concerns**: UI generation separated from business logic

### Testing
- **Test-Driven Development (TDD)**: Write tests before implementing functionality (Red-Green-Refactor cycle)
- **Unit Tests**: 100% code coverage for all classes and methods
- **Edge Cases**: Test all boundary conditions, error scenarios, and invalid inputs
- **Mocking**: Use mocks/stubs for external dependencies (database, file system, etc.)
- **Test Frameworks**: PHPUnit for unit testing
- **Test Structure**: Tests in `tests/` directory with PHPUnit configuration
- **Coverage Reports**: HTML and text coverage reports generated automatically

### Interfaces and Contracts
- **Interfaces**: Define contracts for key components (Validators, Processors, etc.)
- **Abstract Classes**: Provide common implementations where appropriate
- **Traits**: Extract reusable functionality to avoid duplication

## Architecture

### Plugin-Based Extension Architecture
- **Core Dependency Layer**: Extends FA_ProductAttributes core module
- **Service Layer**: Plugin-specific services (VariationService, FrontAccountingVariationService, RetroactiveApplicationService)
- **UI Extension Layer**: Hook-based UI extensions to core attributes tab
- **Business Logic Layer**: Variation-specific domain logic
- **Data Access Layer**: Uses core DAO, extends with variation operations

### Key Components
- **Variation Services**: Domain-specific services for variation management
- **Hook Extensions**: Registered extensions to core hook points
- **UI Components**: Extended UI for variation management
- **Pattern Analysis**: RetroactiveApplicationService for existing product analysis
- **Relationship Management**: Parent-child product hierarchy handling

### Extension Mechanism
- **Hook Registration**: Plugin registers extensions during activation
- **Priority System**: Extensions execute in defined priority order
- **Dependency Injection**: Services receive core dependencies
- **Clean Separation**: Plugin logic isolated from core functionality

## User Acceptance Testing (UAT)

### Test Case Design
- **UI Test Cases**: Based on designed buttons and workflows
- **Integration Test Cases**: End-to-end scenarios
- **Error Handling Test Cases**: Invalid inputs, system failures
- **Performance Test Cases**: Large data sets, concurrent operations

### UAT Scenarios
- CSV import with various file formats and data conditions
- Review and edit functionality
- Bulk update operations
- Error reporting and recovery
- Programmatic API usage by external modules

## Diagrams and Documentation

### UML Diagrams
- **Entity-Relationship Diagram (ERD)**: Database schema and relationships
- **Class Diagrams**: System architecture and class relationships
- **Sequence Diagrams**: Message flow for key use cases
- **Activity Diagrams**: Logic flow charts for complex operations
- **Component Diagrams**: System components and dependencies

### Documentation Standards
- **API Documentation**: Complete API reference for programmatic interfaces
- **Deployment Guide**: Installation and configuration instructions
- **Troubleshooting Guide**: Common issues and solutions
- **Maintenance Guide**: Code structure and modification guidelines

## Implementation Roadmap

### Phase 1: Plugin Foundation
- Define plugin structure and namespace (Ksfraser\FA_ProductAttributes_Variations)
- Set up composer.json with core module dependency
- Create basic hook registration and activation logic
- Establish PSR-4 autoloading structure

### Phase 2: Core Service Development
- Implement VariationService for variation business logic
- Create FrontAccountingVariationService for FA integration
- Develop RetroactiveApplicationService for pattern analysis
- Build comprehensive unit tests for all services

### Phase 3: UI Extension Development
- Create hook extensions for core attributes tab
- Implement variation management UI components
- Develop parent-child relationship displays
- Build admin interface extensions

### Phase 4: Integration and Testing
- Test plugin loading and dependency resolution
- Verify hook extension registration and execution
- Create integration tests with core module
- Develop end-to-end test scenarios

### Phase 5: Documentation and Deployment
- Update plugin-specific documentation
- Create installation and configuration guides
- Package for deployment with dependency checking
- Final testing and validation

### Phase 5: Documentation and Deployment
- Complete PHPDoc documentation
- Create user manuals and API docs
- Package for deployment
- Final testing and validation

## Quality Assurance

### Code Review Checklist
- SOLID principles compliance
- PHPDoc completeness
- Test coverage verification
- Security considerations
- Performance implications

### Continuous Integration
- Automated testing on commits
- Code quality checks (PHPStan, PHPMD)
- Dependency vulnerability scanning
- Documentation generation

## Security Considerations

- Input validation and sanitization
- SQL injection prevention (parameterized queries)
- Access control integration with FrontAccounting
- Audit logging for all operations
- Data integrity checks

## Performance Requirements

- Efficient database queries with proper indexing
- Memory-efficient processing of large CSV files
- Transaction management for data consistency
- Caching where appropriate for repeated operations