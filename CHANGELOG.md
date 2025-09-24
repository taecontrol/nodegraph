# Changelog

All notable changes to `nodegraph` will be documented in this file.

## 0.2.0 - 2025-09-24

**Full Changelog**: https://github.com/taecontrol/nodegraph/compare/0.1.0...0.1.1

- Fix some bugs when resolving state in the Checkpoint model

## 0.1.0 - 2025-09-23

**Full Changelog**: https://github.com/taecontrol/nodegraph/compare/0.0.1...0.1.0

This release introduces first-class multi-graph support. You can now configure multiple independent graphs, each with its own state enum, while sharing the same threads and checkpoints infrastructure. Threads now include a graph_name that determines which enum is used for state casting.

- Multiple graphs configurable via new nodegraph.graphs array
- Per-graph state enum casting (Thread.current_state & Checkpoint.state)
- Explicit graph_name column required when creating threads
- Clear isolation of metadata and events per thread/graph
- Updated README with multi-graph quickstart and advanced section
- Tests updated to reflect new configuration structure

## 0.0.1 - 2025-09-11

- Initial release
